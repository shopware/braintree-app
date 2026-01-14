#!/usr/bin/env bash

set -Eeu -o pipefail

print_err() { local retval=$?; echo "$(basename "$0"): failed at $1: $BASH_COMMAND"; exit $retval; }
trap 'print_err $LINENO' ERR

cd "$(dirname "${BASH_SOURCE[0]}")/.."

if [ -z "${APP_ENV+x}" ]; then
    # shellcheck disable=SC2046
    export $(grep APP_ENV= ./.env)
fi

if [ -n "${CI:-}" ] || [ "$APP_ENV" = "prod" ]; then
    export APP_URL="https://braintree.shopware.com"
elif [ -z "${APP_URL+x}" ]; then
    # shellcheck disable=SC2046
    export $(grep APP_URL= ./.env)
fi

if [ -z "${APP_SECRET+x}" ]; then
    # shellcheck disable=SC2046
    export $(grep APP_SECRET= ./.env)
fi

yq_xml_args="--input-format xml --output-format xml --indent 4 --xml-strict-mode"

_validate_app_setup() {
    if [ ! -d "$1" ]; then
        echo "App version \"$1\" does not exist."
        exit 1
    elif [ ! -f "$1/manifest.xml" ]; then
        echo 'Missing manifest.xml, run "composer apps:setup" first'
        exit 1
    fi
}

setup() {
    echo "APP_ENV=$APP_ENV"
    echo "APP_URL=$APP_URL"

    apps="$(find ./apps -maxdepth 1 -name '6.*' -type d)"

    for app in $apps; do
        cp -R ./apps/shared/. "$app"

        # shellcheck disable=SC2086,SC2016
        yq $yq_xml_args -ir eval-all '. as $item ireduce ({}; . * $item)' "$app/manifest.xml" "$app/manifest.overrides.xml"
        # shellcheck disable=SC2086,SC2016
        yq $yq_xml_args -ir '(.. | select(tag == "!!str")) |= envsubst(nu)' "$app/manifest.xml"

        if [ -z "${CI:-}" ] && [ "$APP_ENV" != "prod" ]; then
            # shellcheck disable=SC2086
            yq $yq_xml_args -ir '.manifest.setup.secret |= env(APP_SECRET)' "$app/manifest.xml"
        fi

        if [[ "${app##*/}" == 6.5* ]]; then
            # shellcheck disable=SC2086
            yq $yq_xml_args -ir 'del(.manifest.gateways)' "$app/manifest.xml"
        fi
    done
}

clean() {
    # shellcheck disable=SC2013
    for ig in $(cat ./apps/.gitignore); do
        find ./apps -wholename "./apps/$ig" -delete -printf "removing: %p\n"
    done

    find ./apps/6.* -type d -empty -delete -printf "removing: %p\n"
}

validate() {
    error=false
    apps="$(find ./apps -maxdepth 1 -name '6.*' -type d)"

    for app in $apps; do
        _validate_app_setup "$app"

        # Validate absence of development content
        if [ -n "${CI:-}" ] || [ "$APP_ENV" == "prod" ]; then
            if ! grep -q 'https://braintree.shopware.com' "$app/manifest.xml"; then
                error=true
                echo "::error file=$app/manifest.xml,line=1::Contains no production URL"
            fi

            if ! command -v shopware-cli &> /dev/null; then
                echo "shopware-cli could not be found"
                exit 1 
            fi

            if ! shopware-cli extension validate --reporter github "$app" 2>/dev/null | sed -n "s|file=|file=$app/|p"; then
                error=true
            fi
        fi

        # Validate schema
        schema="$(yq '.manifest["+@xsi:noNamespaceSchemaLocation"]' "$app/manifest.xml")"
        schemaFile="$(mktemp)"

        curl -so "$schemaFile" "$schema"

        if ! xmllint --quiet --schema "$schemaFile" "$app/manifest.xml" --noout; then
            error=true
        fi
    done

    if [ "$error" = true ]; then
        exit 1;
    fi
}

build() {
    if [ -z "${1+x}" ]; then
        apps="$(find ./apps -maxdepth 1 -name '6.*' -type d)"
        for app in $apps; do
            build "${app##*/}"
        done
    else
        _validate_app_setup "./apps/$1"
        shopware-cli extension build "./apps/$1"
    fi
}

zip() {
    if [ -z "${1+x}" ]; then
        apps="$(find ./apps -maxdepth 1 -name '6.*' -type d)"

        for app in $apps; do
            zip "${app##*/}"
        done
    else
        _validate_app_setup "./apps/$1"
	    shopware-cli extension zip "./apps/$1" --disable-git --filename "SwagBraintreeApp-$1.zip"

        if ! (( "$(du "SwagBraintreeApp-$1.zip" | awk '{print $1}')" < 5120 )); then
            echo "Zip file should not be larger then 5MiB"
            exit 1
        fi
    fi
}

help() {
    echo "Usage: $(basename "$0") <setup | clean | validate | zip [version?] | build [version?]>"
    exit 1
}

if [ -z "${1+x}" ]; then help; fi

case "$1" in
    "setup")
        setup
        ;;
    "clean")
        clean
        ;;
    "validate")
        validate
        ;;
    "zip")
        shift
        zip "$@"
        ;;
    "build")
        shift
        build "$@"
        ;;
    *)
        help
        ;;
esac
