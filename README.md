```
      ___           ___           ___           ___
     /  /\         /  /\         /  /\         /  /\
    /  /:/        /  /:/_       /  /:/        /  /::\
   /  /:/        /  /:/ /\     /  /:/        /  /:/\:\
  /  /:/  ___   /  /:/ /::\   /  /:/  ___   /  /:/~/::\
 /__/:/  /  /\ /__/:/ /:/\:\ /__/:/  /  /\ /__/:/ /:/\:\
 \  \:\ /  /:/ \  \:\/:/~/:/ \  \:\ /  /:/ \  \:\/:/__\/
  \  \:\  /:/   \  \::/ /:/   \  \:\  /:/   \  \::/
   \  \:\/:/     \__\/ /:/     \  \:\/:/     \  \:\
    \  \::/        /__/:/       \  \::/       \  \:\
     \__\/         \__\/         \__\/         \__\/
****Developed by the Spring 2020 Programming Capstone Class****
```
# Computer Science Course Assistance (CSCA)

## 1. Project Background:

We had a bit of an uphill battle with this one. The Covid-19 pandemic hit the week we were set to start. All of the
work the group has done has been remote. The project management team had to salvage the situation and coordinate the
group in order to keep everything on the rails. With that, there are many things we could have done better. If you're
reading this, you might be the person for that job.

The Computer Science Course Assistance (CSCA) site was designed to be a tool for first year programming students to
use to seek answers to common questions and find solutions to simple problems. These are things like getting the
software they need set up on their PCs and references to syntax and formatting for certain programming languages.

Our team makeup was as follows:

- Sarah Wilson - Project Lead, Backend Developer
- Gerald Burke - Project Lead, Backend Developer
- Nataliya Chibizova - Assistant Lead, Frontend Developer, Content Production
- Jonas Foglesong - Assistant Lead, Video Production
- Jeremy Vines - Video Production
- Anthony Wernock - Content Production, Video Production
- Ayush Patel - Content Production, Video Production
- Dawson Busby - Content Production
- Aaron Barker - Content Production
- Jonathan Rogers - Content Production, Frontend Developer
- Wesley Persinger - Backend Developer, Design

Sarah in particular designed our database and roughed out our initial design, building out the framework that made
the rest of the project possible. She also put out a lot of the fires that everyone else on the project caused.
On behalf of all of the rest of the team, we extend a special shout-out to her.

## 2. The Site Structure:

The structure of the site was intended to be fairly straightforward. We have a simple, but robust content management
system in place that allows users to add, edit, or delete courses, topics, and users. To access this, simply hit the
'Login' link at the top right corner of the page, just below the search.

When you get into the admin portal, you will see a number of cards with input fields and tables. The first of these
labeled 'Settings' will change the site name as it appears in the header above the navigation bar and the welcome
message that is displayed at the top of the site's index page.

The second card labeled 'Manage Users' contains our user management system. Pressing the 'Add New User' button will
open a modal (in-window popup) that will offer the user an opportunity to create a new user. If a user already exists
an error message will appear and inform the user. Pressing the edit button will allow the user to modify user settings
including username, password, and admin status. As a note, a user can also click their name in the navigation bar at
the top of the page to change their password quickly. The trash-can icon will allow a user to delete another.
THIS IS PERMANENT!!!! Do not do this unless you are 100 percent sure you will never need the user again.

The third card labeled 'Manage Courses' is where users go to manage the information on the main site page. Clicking
'Add New Course' will open a modal that allows the user to add a new course. New courses will be generated with a
generic topic. This is due to the way we currently handle the listing of courses. This topic can be changed, but
cannot be deleted for the reason stated. When we edit a course, all we're editing is the name, or the 'Header', and
the description that appears below the header on the course tab. Deleting Courses is serious business. I'm going to
write the rest of this in caps to show you how serious. WHEN YOU DELETE A COURSE, YOU DELETE ALL TOPICS UNDER THE
COURSE. THIS ACTION CANNOT BE UNDONE, YOU WILL LOSE THE DATA FOREVER! Maybe not forever, but don't count on the back-
up being nearly recent enough to prevent a lot of lost work.

If you click on a course directly, you are taken to the 'topics' page. The topics page works much like the other lists
we've seen with a few notable exceptions. Choosing to 'Add New Topic' or edit an existing topic will take you to a
dedicated topic page. On this page, we'll have access to a rich-text editor. This is handy, as it allows us to perform
special formatting on our posts, including bolded text and images. At the bottom of the topic form is a video link
field. The only thing that should ever go here is a YouTube link. You can get the link from the browser bar on the
video. Do not get an embed code, as we turn the link into an embed code before we store it on the server. From the
topics page, you can also move topics up or down. This impacts the order in which they appear under their Course tab
on the main site page.

For our rich-text editor we use Quill. They have an open-source license and their documentation can be found here.
https://quilljs.com/
A note about how the editor works. We use an invisible input field in a form and assign the Base-64 encoded contents
of the editor box to the value of the invisible input field so it can be posted when we submit the form. We do this
in JavaScript using a JQuery event listener.

## 3. The Backend:

This is where most of the heavy lifting on the site proper happened. Below, I'm going to list a rough sketch of our
tables and their fields.

```
COURSES                  COURSE_TOPICS                COURSE_VIDEOS                   USERS
----------------------   ---------------------------  ------------------------------  ---------------------------
COURSE_ID      int(11)   COURSE_TOPIC_ID     int(11)  COURSE_VIDEO_ID        int(11)  USER_ID             int(11)
NAME      varchar(100)   TOPIC_NUMBER        int(11)  DESCRIPTION       varchar(500)  USER_NAME       varchar(50)
SUMMARY  varchar(4000)   COURSE_ID           int(11)  VIDEO_EMBED_CODE varchar(4000)  PASSWORD_HASH  varchar(512)
DISPLAY_ORDER  int(11)   DISPLAY_ORDER       int(11)                                  IS_ACTIVE        tinyint(1)
                      HEADER         varchar(256)                                  IS_ADMIN         tinyint(1)
                      CONTENT       varchar(4000)
                      COURSE_VIDEO_ID     int(11)
```

All of our table and column names are in all-caps, or 'Shouty-Case'.

Courses is where we store our course information. We have an auto-incremented ID, a name, a summary, and a display
order. That display order is never publicly exposed in the cms, but is used to determine what order they appear in
on the main page. The primary key for courses is COURSE_ID and the table has a one-to-many relationship with
COURSE_TOPICS on COURSE_ID.

COURSE_TOPICS is much the same. The primary key is COURSE_TOPIC_ID and it has a one-to-one relationship with
COURSE_VIDEOS on COURSE_VIDEO_ID. Essentially, we opted to keep the video content separated from the main topic
table to help simplify an already relatively complicated table. We don't currently use the video DESCRIPTION for
anything, but it's there in case we need to use it in the future.

The USERS table stores all our login information for our users. We check against this when logging in. A notable
column here is the PASSWORD_HASH. We hash our passwords for the sake of security, as it's incredibly risky to keep
sensitive user data unencrypted on the server.

The next question would be: How are we accessing this data? The answer is emphatically "With PHP"! We utilize the
PDO object in PHP to put together SQL statements, execute them on the database, then return the results to the client
side of the application. As of right now, almost all of this functionality is in the file 'db.php'. This is a bad
practice, but it worked to get things moving. We can separate out the functionality in the future.

Every function (or method) in db.php is designed to interface with the database. These functions are then called by
the various pages to get the data they need, or perform the actions they need to perform. I'm going to run through
a quick list of these functions as they are now.

- get_pdo: This creates a PDO object connected to our database. We want to instantiate and then nullify the PDO object
before and after each use.

- validate_user: This checks user information against values stored in the USERS table in the database. If there's a
valid match, it will return the user id and whether the user is an admin in the form of an array.

- get_courses: This is the workhorse of db.php. This returns an array of all the courses and attached topics that are
currently in the database. One quirk with this statement is that it will not return a course with no topics.

- get_settings: This gets and returns the site header and description that appear on the main site page. We use this
to populate the form at the top of the admin page with current values.

- update_settings: This updates the settings in the database if changes are made and the form is submitted.

- add_course: This adds a course to the COURSES table. It also adds a topic to the COURSE_TOPICS table that is attached
to the created course. We do this to avoid the course being missed by get_courses.

- edit_course: Updates information for the currently selected course.

- delete_course: This not only deletes the course, but it goes through every topic attached to the course and deletes
them as well using the delete_topic function.

- add_topic: This one is a little bit different than add_course, as it has a few different statements that run based
on whether or not the user has specified a video to be added to the topic.

- edit_topic: This works much the same as add_topic, in that we do things differently based on whether or not we're
using a video.

- delete_topic: This one does what's on the tin. It also shifts up the order of every topic below it so things don't get
out of order.

- add_user: Adds a user to the USERS table.

- set_user_password: This one is called when the user changes their password. This one uses password hashing for
security.

- get_users: This returns an array of users from the USERS table. It grabs every column except the PASSWORD_HASH one
for reasons that should be obvious.

- delete_user: Removes the selected user from the table.

- edit_user: This one allows you to edit all user information except for their password.

- move_topic: This one swaps the placement of topics using display order.

## 4. Areas of Opportunity:

There are some pretty big ones here. If you're inheriting this project, I wish you the very best of luck. I know
you'll have some ideas of your own, but here are some of the things we'd liked to have done, or perhaps could have
done better.

### Frontend Design: 

Sarah threw together a really great template based on early wireframes that Wesley put together.
This worked great for us to get our data in and get the functionality of the content management system going, but
we really hoped we could get a little bit more razzle-dazzle going on the design front. If you have a really design
minded person on your team, getting the front end prettier and more user-friendly would be a really great place to
start.

There's an issue with the way that content is displayed. When images are posted in the body of a topic, they can
appear underneath the embedded video.

### Backend Refactoring: 

There are some big ones here. We have all of our database interface functionality running in
a single script. This is bad for readability, code maintenance, and bug-fixing. If you could refactor db.php and
separate out a lot of the functionality, that would be a lot better.

Another opportunity on the backend is another select statement, perhaps more than that, to get courses and topics
in a more specific way so we're not taking a shotgun approach every time we grab them. For me, this was a significant
bit of tech debt. Sarah set up the get_courses statement and I worked so hard to just use the one she wrote, that it
took me longer to get the same functionality I would have if I'd just written a bespoke statement for my task.

### Features: You'll see this commented out at the bottom of the cards, but we never got the chance to get pagination
working. This will only really become a problem if we start to have a lot of courses or, more likely, topics.

We don't have a reliable system for password recovery at the moment. If no one can get logged into the admin portal
it'll get stuck and you'll have to make manual changes to the site's files to fix that. Most sites handle password
recovery using a mail server, but we didn't have access and we didn't really have time to get access.

### Project Management: 

This was a huge challenge for Sarah and I. Without Slack, we likely wouldn't have gotten half
as far as we did. That said, if we had implemented version control like Git at the start of the project, we would
have had a much easier time. Please set up version control if you take this over. Also, Slack and Trello are your
friends, even if you're working together in-person fairly often. Start these things before you write a single line
of code. You will thank me.

## 5. The Future:

With any luck, the world will still exist by the time you're reading this. If you are, I did my very best to address
every issue you may have. If there's a problem that you blame us for, or you need to ask me a specific question I
didn't cover here, you can e-mail me @ burkeg@goldmail.etsu.edu. I know it's an archaic ETSU email, it just happens
to be the only one I ever bothered to wire up to my phone. Please let me know that you're a Capstone student in the
subject or I will likely ignore it.
