#!/usr/bin/env bash
set +e

mkdir -p /tmp/log/nginx/
mkdir -p /tmp/var/nginx/

escape_nginx_value() {
  printf '%s' "$1" \
    | sed \
      -e 's/\\/\\\\/g' \
      -e 's/"/\\"/g' \
      -e 's/\$/\\$/g' \
      -e 's/&/\\\&/g' \
      -e 's/|/\\|/g'
}

replace_placeholder() {
  local placeholder="$1"
  local value="$2"
  local escaped_value

  escaped_value="$(escape_nginx_value "$value")"
  sed -i "s|${placeholder}|${escaped_value}|g" /code/nginx.conf
}

echo "start php-fpm"
php-fpm7.4 -c /code/php.ini -y /code/php-fpm.conf

echo "start nginx"

replace_placeholder "PHDB_HOST" "${DB_HOST:-}"
replace_placeholder "PHDB_NAME" "${DB_NAME:-}"
replace_placeholder "PHDB_USER" "${DB_USER:-}"
replace_placeholder "PHDB_PASSWORD" "${DB_PASSWORD:-}"

replace_placeholder "PHFRONTEND_URL" "${FRONTEND_URL:-}"
replace_placeholder "PHALLOW_NO_ORIGIN_REQUESTS" "${ALLOW_NO_ORIGIN_REQUESTS:-}"

replace_placeholder "PHTZ" "${TZ:-}"
replace_placeholder "PHGLOBAL_SALT_3" "${GLOBAL_SALT_3:-}"

replace_placeholder "PHDEFAULT_FIELDS" "${DEFAULT_FIELDS:-}"

replace_placeholder "PHFILE_ENABLED" "${FILE_ENABLED:-}"
replace_placeholder "PHALLOW_SIGN_UP" "${ALLOW_SIGN_UP:-}"
replace_placeholder "PHCUSTOMIZE_FIELDS" "${CUSTOMIZE_FIELDS:-}"

replace_placeholder "PHPIN_EXPIRE_TIME" "${PIN_EXPIRE_TIME:-}"
replace_placeholder "PHLOG_EXPIRE_TIME" "${LOG_EXPIRE_TIME:-}"

replace_placeholder "PHBLOCK_IP_TRY" "${BLOCK_IP_TRY:-}"
replace_placeholder "PHBLOCK_IP_TIME" "${BLOCK_IP_TIME:-}"

replace_placeholder "PHBLOCK_ACCOUNT_TRY" "${BLOCK_ACCOUNT_TRY:-}"
replace_placeholder "PHACCOUNT_BAN_TIME" "${ACCOUNT_BAN_TIME:-}"

replace_placeholder "PHSERVER_TIMEOUT" "${SERVER_TIMEOUT:-}"
replace_placeholder "PHPBKDF2_ITERATIONS" "${PBKDF2_ITERATIONS:-}"

nginx -c /code/nginx.conf -g "daemon off;"