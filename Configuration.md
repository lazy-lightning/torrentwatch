Note this information is for 0.6.X Series.  Documentation for 0.7.X is still to be written.

## Feeds ##
### Adding a Feed ###
  * To add a feed, type the url in the centered box under "New RSS Feed" at the bottom
  * To add a feed you MUST also add your first match.
  * If you just want to look at the feed without matching anything, put in a match that will never work(i.e. asdf asdf)
  * Click the orange + button next to the boxes under your feed
### Deleting a Feed ###
  * To Delete a feed delete all the matches from the feed


## Matches ##
### What is a Match ###
  * each feed has a set of matches attached to it
  * a match consists of 2 strings(the left and right on the config page)
  * to get a hit from a feed BOTH the strings must match the title of the feed item
  * The first string, known as the key, must be unique for its feed. The second string can be anything.
  * The matching is case in-sensitive
#### The Strings ####
  * The strings don't support globs(`*`) but do support regexp(.`*`)
  * So .`*` is the equivalent of what you would expect from `*`
  * i.e. "a.`*`d" will match "abcd" or even "abra cadabra" but "a`*`d" means something [completely different](http://www.regular-expressions.info/repeat.html)

### Adding a Match ###
#### Automatic Matching ####
  * To add a match you can click on a torrents name from the DL Now or Test Matches page.
  * This will attempt to guess the show title and rip type to match later
  * Specifically this is for TV Episodes and wont generate a match on other torrents
  * After adding a match always use the test matches to verify your shows get matched
#### Manual Matching ####
  * Say you want to download all releases of Big Brother USA from release group XXxxX
  * Pull up the test matches page, and find a torrent from the series
  * Make note of how the title is displayed(Big.Brother.US or Big Brother: USA etc)
  * Go to the Configuration Page
  * Under each feed is a list of matches, and then two empty boxes
  * in the left box, enter the title as displayed on the test matches page
  * in the right box, enter the release group/rip info as displayed in the title
  * for example "Big.Brother.US" on the left and XviD-XXxxXX on the right
  * Click the orange + button to the right of the boxes
  * After adding a match always use the test matches to verify your shows get matched
  * Really the two strings can be any two strings that match the release title, play arround!
### Deleting a Match ###
  * To delete a match press the orange - button next to it

## [Feedrinse.com](http://www.feedrinse.com) ##
Another option for generating matches is to do it externally, with feedrinse.com.  Feedrinse has a nice interface for pruning down numerous feeds into a single feed of just the things you want.  You can then subscribe to that feed and tell torrentwatch to grab everything from the feed(use .`*` .`*` as your two strings).  An added bonus is you can adjust your matches from anywhere.

## Other Notes ##
The Test Matches and DL Now links go to the same page but they work slightly different
  * Test Matches will never download a torrent.  It will just tell you what is matching. This allows you to see if your new match actually works right
  * DL Now will download any new matching torrents and start them with the PCH's bittorrent program.
New matching torrents are also checked for at 30 minutes past the hour every hour.
  * If you have put any of your own torrents in the /share/.torrents/ folder, they will also get started at 30 minutes past the hour.
All Feeds are cached localy for 50 minutes before a new one is fetched.
  * Please be patient when pulling up the DL Now and Test Matches page. If we have to fetch new feeds we are looking at 3-5s per feed to fetch and parse them.