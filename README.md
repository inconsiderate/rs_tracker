# [Royal Road Watch](https://royalroadwatch.site)
Get notified the moment your story graces Rising Stars.

Survival Stats: Track how long you hold onto your throne — whether it's hours, days, or gasp YEARS?!

Because every author deserves to know when they're a star — even if it's only for 15 minutes.

After registration you'll be able to track each of your stories on Royal Road, and receive an alert when you make it to any of the Rising Stars lists. Add your story URL and you'll see your stats. When you get on any Rising Stars list, it'll be tracked in the database and you'll know how long you stayed on it. If you fall off the list and get back on later, you'll see that too!

## Local dev setup

- Clone this repo
- bring up the docker containers
  - `docker compose up --build`
- install composer packages
  - `docker exec -it rs_tracker_app sh -c "composer install"`
- run doctrine migrations and populate local test database
  - `docker exec -it rs_tracker_app sh -c "php bin/console doctrine:migrations:migrate"`
- project should now be visible at http://localhost:9876
  

### run the checker, locally
- `html\src\Scheduler\MainSchedule.php` frequency is set to 30 seconds but you can change this locally if you want
- `docker exec -it rs_tracker_app sh -c "php bin/console messenger:consume -v scheduler_listCheck"`
- make sure to ctrl+c to stop the process after it runs once. You don't want to let it keep going every 30s

### crons
- update all the current story positions: */15	*	*	*	*
  - `cd /home/royahjos/ && /usr/local/bin/php /home/royahjos/bin/console app:dispatch-check-stars-lists >> /home/royahjos/logs/messenger.log`
