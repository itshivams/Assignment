#!/bin/bash
# This script should set up a CRON job to run cron.php every 24 hours.

CRON_CMD="0 0 * * * /usr/bin/php $(pwd)/cron.php"
# add if not present
(crontab -l 2>/dev/null | grep -F "$CRON_CMD") || \
  (crontab -l 2>/dev/null; echo "$CRON_CMD") | crontab -
echo "CRON job installed: $CRON_CMD"
