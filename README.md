
# Local dev setup

- Clone this repo
- bring up the docker containers
  - `docker compose up --build`
- install composer packages
  - `docker exec -it rs_tracker_app sh -c "composer install"`
- run doctrine migrations and populate local test database
  - `docker exec -it rs_tracker_app sh -c "php bin/console doctrine:migrations:migrate"`
- project should now be visible at http://localhost:9876
  

## run the checker, locally
- `html\src\Scheduler\MainSchedule.php` frequency is set to 15 seconds but you can change this locally if you want
- `docker exec -it rs_tracker_app sh -c "php bin/console messenger:consume -v scheduler_listCheck"`
- make sure to ctrl+c to stop the process after it runs once. You don't want to let it keep going every 15s
