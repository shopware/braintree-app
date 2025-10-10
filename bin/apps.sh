#!/usr/bin/env bash

set -Eeu -o pipefail

print_err() { local retval=$?; echo "$(basename "$0"): failed at $1: $BASH_COMMAND"; exit $retval; }
trap 'print_err $LINENO' ERR

cd "$(dirname "${BASH_SOURCE[0]}")/.."

if [ -z "${APP_URL+x}" ]; then
    export $(grep APP_URL= ./.env)
fi

if [ -z "${APP_ENV+x}" ]; then
    export $(grep APP_ENV= ./.env)
fi

if [ -z "${APP_SECRET+x}" ]; then
    export $(grep APP_SECRET= ./.env)
fi

setup() {
    echo "APP_ENV=$APP_ENV"
    echo "APP_URL=$APP_URL"

    xml_args="--input-format xml --output-format xml --indent 4 --xml-strict-mode"
    apps="$(find ./apps -maxdepth 1 -name '6.*' -type d)"

    for app in $apps; do
        cp -R ./apps/shared/. "$app"

        yq $xml_args -ir eval-all '. as $item ireduce ({}; . * $item)' "$app/manifest.xml" "$app/manifest.overrides.xml"
        yq $xml_args -ir '(.. | select(tag == "!!str")) |= envsubst(nu)' "$app/manifest.xml"

        if [ -z "${CI+x}" ] && [ "$APP_ENV" == "dev" ]; then
            yq $xml_args -ir '.manifest.setup.secret |= env(APP_SECRET)' "$app/manifest.xml"
        fi
    done
}

clean() {
    for ig in $(cat ./apps/.gitignore); do
        find ./apps -wholename "./apps/$ig" -delete -printf "removing: %p\n"
    done
}

validate() {
    error=false
    apps="$(find ./apps -maxdepth 1 -name '6.*' -type d)"

    for app in $apps; do
        # Validate absence of development content
        if [ -n "${CI+x}" ] || [ "$APP_ENV" != "dev" ]; then
            if ! grep -q 'https://braintree.shopware.com' "$app/manifest.xml"; then
                error=true
                echo "::error file=$app,line=1::Contains no production URL"
            fi

            if ! command -v shopware-cli &> /dev/null; then
                echo "shopware-cli could not be found"
                exit 1 
            fi

            if ! shopware-cli extension validate --reporter github --store-compliance "$app" 2>/dev/null | sed -n "s|file=|file=$app/|p"; then
                error=true
            fi
        fi

        # Validate schema
        schema="$(yq '.manifest["+@xsi:noNamespaceSchemaLocation"]' "$app/manifest.xml")"
        schemaFile="$(mktemp)"

        wget -q "$schema" -O "$schemaFile"

        if ! xmllint --schema "$schemaFile" "$app/manifest.xml" --noout; then
            error=true
        fi
    done

    if [ "$error" = true ]; then
        exit 1;
    fi
}

help() {
    echo "Usage: $(basename "$0") <setup | clean | validate>"
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
    *)
        help
        ;;
esac
