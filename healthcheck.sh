#!/bin/bash
mysqladmin ping -h "${MYSQL_HOST:-localhost}" -u"${MYSQL_USER:-root}" -p"${MYSQL_PASSWORD}" > /dev/null 2>&1