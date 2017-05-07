#!/usr/bin/env bash

SCRIPT_DIR=$(dirname $0)

DATABASE_NAME=raticals

READER_USERNAME=${DATABASE_NAME}_reader
READER_PASS="$(openssl rand -base64 12)"

ADMIN_USERNAME=${DATABASE_NAME}_admin
ADMIN_PASS="$(openssl rand -base64 12)"

echo "Please enter root user MySQL password!"
read rootpasswd

SQL="mysql -uroot -p${rootpasswd} -e"

$SQL "DROP DATABASE IF EXISTS $DATABASE_NAME;" || exit $?
$SQL "CREATE DATABASE ${DATABASE_NAME} /*\!40100 DEFAULT CHARACTER SET utf8 */;" || exit $?

#Create users with appropriate permissions.
$SQL "GRANT USAGE ON *.* TO '$READER_USERNAME'@'localhost';"
$SQL "DROP USER $READER_USERNAME@localhost" || exit $?
$SQL "CREATE USER $READER_USERNAME@localhost IDENTIFIED BY '$READER_PASS';" || exit $?
$SQL "GRANT SELECT ON $DATABASE_NAME.* TO '${DATABASE_NAME}'@'localhost';" || exit $?

$SQL "GRANT USAGE ON *.* TO '$ADMIN_USERNAME'@'localhost';"
$SQL "DROP USER $ADMIN_USERNAME@localhost" || exit $?
$SQL "CREATE USER $ADMIN_USERNAME@localhost IDENTIFIED BY '$ADMIN_PASS';" || exit $?
$SQL "GRANT SELECT,UPDATE,INSERT ON $DATABASE_NAME.* TO '${DATABASE_NAME}'@'localhost';" || exit $?

$SQL "FLUSH PRIVILEGES;" || exit $?

mysql -uroot -p${rootpasswd} $DATABASE_NAME < $SCRIPT_DIR/raticals_schema.txt || exit $?

echo $ADMIN_PASS > $SCRIPT_DIR/../${ADMIN_USERNAME}_password.txt
echo $READER_PASS > $SCRIPT_DIR/../${READER_USERNAME}_password.txt
