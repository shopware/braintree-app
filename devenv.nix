{ pkgs, lib, config, ... }:

let
  pcov = config.languages.php.package.buildEnv {
    extensions = { all, enabled }: with all; (builtins.filter (e: e.extensionName != "xdebug") enabled) ++ [config.languages.php.package.extensions.pcov];
    extraConfig = config.languages.php.ini;
  };
in {
  packages = [
    pkgs.gnupatch
    pkgs.nodePackages_latest.yalc
    pkgs.gnused
    ( pkgs.writeShellScriptBin "php-pcov" ''
      export PHP_INI_SCAN_DIR=''${PHP_INI_SCAN_DIR-'${pcov}/lib'}
      exec -a "$0" "${pcov}/bin/.php-wrapped"  "$@"
    '')
  ];

  dotenv.disableHint = true;

  languages.javascript = {
    enable = lib.mkDefault true;
    package = lib.mkDefault pkgs.nodejs-18_x;
  };

  languages.php = {
    enable = lib.mkDefault true;
    version = lib.mkDefault "8.2";
    extensions = [ "pcov" ];

    ini = ''
      memory_limit = 2G
      realpath_cache_ttl = 3600
      session.gc_probability = 0
      display_errors = On
      error_reporting = E_ALL
      assert.active = 0
      opcache.memory_consumption = 256M
      opcache.interned_strings_buffer = 20
      zend.assertions = 0
      short_open_tag = 0
      zend.detect_unicode = 0
      realpath_cache_ttl = 3600
    '';

    fpm.pools.web = lib.mkDefault {
      settings = {
        "clear_env" = "no";
        "pm" = "dynamic";
        "pm.max_children" = 10;
        "pm.start_servers" = 2;
        "pm.min_spare_servers" = 1;
        "pm.max_spare_servers" = 10;
      };
    };
  };

  services.caddy = {
    enable = lib.mkDefault true;

    virtualHosts.":8080" = lib.mkDefault {
      extraConfig = lib.mkDefault ''
        @default {
          not path /theme/* /media/* /thumbnail/* /bundles/* /css/* /fonts/* /js/* /sitemap/*
        }

        root * public
        php_fastcgi @default unix/${config.languages.php.fpm.pools.web.socket} {
            trusted_proxies private_ranges
        }
        file_server
      '';
    };
  };

  services.mysql = {
    enable = true;
    package = pkgs.mysql80;
    initialDatabases = lib.mkDefault [{ name = "swagbraintree"; }];
    ensureUsers = lib.mkDefault [
      {
        name = "swagbraintree";
        password = "swagbraintree";
        ensurePermissions = {
          "swagbraintree.*" = "ALL PRIVILEGES";
          "swagbraintree_test.*" = "ALL PRIVILEGES";
        };
      }
    ];
    settings = {
      mysqld = {
        log_bin_trust_function_creators = 1;
        port = 3307;
      };
    };
  };

  # Environment variables

  env.APP_URL = lib.mkDefault "http://localhost:8080";
  env.APP_SECRET = lib.mkDefault "devsecret";
  env.DATABASE_URL = lib.mkDefault "mysql://swagbraintree:swagbraintree@localhost:3307/swagbraintree";
}
