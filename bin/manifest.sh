#!/usr/bin/env bash

set -Eeu -o pipefail

print_err() { local retval=$?; echo "Failed at $1: $BASH_COMMAND"; exit $retval; }
trap 'print_err $LINENO' ERR

cd "$(dirname "${BASH_SOURCE[0]}")/.."

help() {
    echo "Usage: $(basename "$0") <configure | clean | validate-prod>"
    exit 1
}

configure() {
    if [ -z "${APP_URL+x}" ]; then
        export $(grep APP_URL= ./.env)
    fi

    if [ -z "${APP_ENV+x}" ]; then
        export $(grep APP_ENV= ./.env)
    fi

    if [ -z "${APP_SECRET+x}" ]; then
        export $(grep APP_SECRET= ./.env)
    fi

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

validate_prod() {
    error=0
    apps="$(find ./apps -maxdepth 1 -name '6.*' -type d)"

    for app in $apps; do
        if ! grep -q 'https://braintree.shopware.com' "$app/manifest.xml"; then
            error=true
            echo "::error file=$app,line=1::Contains no production URL"
        fi

        if grep -q '<secret>' "$app/manifest.xml"; then
            error=true
            echo "::error file=$app,line=1::Contains a secret"
        fi
    done

    if [ "$error" = true ]; then
        exit 1;
    fi
}

if [ -z "${1+x}" ]; then help; fi

case "$1" in
    "configure")
        configure
        ;;
    "clean")
        clean
        ;;
    "validate-prod")
        validate_prod
        ;;
    *)
        help
        ;;
esac
