# [Royal Road Watch [Meta-Analytics, Reincarnation]](https://royalroadwatch.site)

https://royalroadwatch.site
### Genres:
**Survival Drama // Statistical Progression // Meta-Writing // Why Isn’t My Story Trending?!**

Quietly walking home one day after work, Rising Stars was unexpectedly hit by a truck! Now they’ve awakened in a strange new world, a world made of lists and genres and wordsmiths of every size and shape. Granted the powerful unique class ‘Watcher’, the newly christened Royal Road Watch (version 0.1) will need to adapt (and push out new versions) if they hope to survive! Armed with nothing but their own will to write, and a desire to climb the genre lists, Royal Road Watch will vow to defeat the evil Queen Procrastination and come out on top!

### What You Can Expect:
Instant Glory Alerts: Get notified via email the moment your story graces Rising Stars, and again when you hit the coveted #1 slot!
Survival Stats: Track how long you hold onto your throne — whether it’s hours, days, or *gasp* YEARS?!
Straightforward Design: Just the info you actually care about.
Attentive Dev: Want a new feature? Just ask!

*Because every author deserves to know when they’re a star — even if it’s only for 15 minutes.*

# What is it really though?
RoyalRoadWatch is a webapp which tracks every story that hits any of the Rising Stars lists, the highest position they get to, and how long they stay on the list.

If you fall off the list and get back on later, you’ll see that too! 

**Without an account** (free!): You can search for any story and find out all its tracked stats on the 15 main genre lists, as well as the Main Rising Stars list.

**With an account** (still free!): You can save specific stories to be watched. When one of those stories reaches at least 20th position on any of the 15 main genre lists, or the Main list, you’ll get an email notifying you.

**With a supporter account** (tied to patreon!): All the same as above, but also view and track the 49 super secret hidden Rising Stars genre lists, as well as get notifications starting at position 50 on any list!
Don't want to pay? You can still see all this stuff from the free search box, but it's slightly less convenient.

# Notes
- It’s all pretty basic looking sorry, I’m not a designer, I’m a builder.
- Data collection started on Dec 23rd 2024 when I launched the app, so you won’t be able to look up your old stories (future you won’t have that problem though).
- The email to sign up is only used for emailing you a notification when/if you hit the RS list. **Nothing else.** If you don’t trust me, the code is open source. :heart:
- I’ll probably add a bunch of analytics and statistics later, in the future, maybe, I dunno. Anything I update will go in this thread!
- If you want anything added or if something is messed up, let me know!
.
Find the app here: https://royalroadwatch.site
Development info: https://github.com/users/inconsiderate/projects/1/views/1

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


NO LONGER ACTIVE:

- update daily RS records: 5	*	*	*	*
  - `cd /home/royahjos/ && /usr/local/bin/php /home/royahjos/bin/console app:dispatch-daily-stars-lists >> /home/royahjos/logs/messenger.log`
  - runs at 9pm PST
