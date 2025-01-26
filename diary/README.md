
# PHP MySQL Diary

- [PHP MySQL Diary](#php-mysql-diary)
  * [Background](#background)
    + [Disclaimer](#disclaimer)
  * [Folder / File Structure](#folder---file-structure)
  * [Installation](#installation)
    + [Download zip file, unzip it, rename it, and move to server](#download-zip-file-unzip-it-rename-it-and-move-to-server)
    + [Create the database](#create-the-database)
    + [Update `settings.php` file](#update--settingsphp--file)
    + [Create Admin User](#create-admin-user)
  * [Accessing the diary website](#accessing-the-diary-website)
  * [Using the diary](#using-the-diary)
    + [Diary Entry / Edit / Delete Page](#diary-entry---edit---delete-page)
      - [Diary Page Screenshots](#diary-page-screenshots)
    + [Categories Page](#categories-page)
      - [Category Page Screenshot](#category-page-screenshot)
    + [Group By Page](#group-by-page)
      - [Group By Page Screenshot](#group-by-page-screenshot)
    + [Day Range Page](#day-range-page)
      - [Day Range Page Screenshot](#day-range-page-screenshot)
    + [Same Day Page](#same-day-page)
      - [Same Day Page Screenshot](#same-day-page-screenshot)
    + [Search Page](#search-page)
      - [Search Page Screenshot](#search-page-screenshot)
    + [Common links for all diary entries](#common-links-for-all-diary-entries)
    + [Navigation Links](#navigation-links)
  * [Validation](#validation)
  * [Data Stuff](#data-stuff)
  * [Issues](#issues)

---

## Background

- I used to write a diary in a notebook.
- To start with, I made a diary website using Classic ASP.
- Later on I made a PHP version, and I have tidied that up and put it here on Github in case it's of use to anyone else.
- A simple PHP diary using a MySQL database to store diary entries.
- Setup was done on a server running:
	- PHP 8.2.5
	- MySQL 5.7.44
- I think it will work on newer versions of MySQL and it also works on PHP 7.3.13. I don't know about older MySQL / PHP Versions though.

### Disclaimer

- The way I have written the PHP code in this diary is pretty crap for the reasons mentioned below.
- If a real programmer were to look at the code they'd probably find countless bugs, issues, shout-out-loud errors and stupid ways of doing things.
- Lots of things are repeated, not a lot is re-used, so I haven't followed the [Don't Repeat Yourself](https://en.wikipedia.org/wiki/Don%27t_repeat_yourself) rule.
- I think I have taken a [Procedural approach](https://stackoverflow.com/questions/1530868/simple-explanation-php-oop-vs-procedural) to writing the PHP as well, which is not ideal.
- There is also lots of [spaghetti code](https://t2informatik.de/en/smartpedia/spaghetti-code/), so all in all while this crappy diary might work, there are 1,001 reasons why it shouldn't be used as an example of the correct way to do things.
- For my basic hobbyist needs it works for me so I have put the code on Github in case anyone else finds it useful.
- If I get my head around using Object-Oriented programming in PHP I will have a go at improving the code in this project.

## Folder / File Structure

- Once the zip file has been downloaded and unzipped, the `diary` folder contains the following (not all files and folders are shown for the sake of readability):

```
+---📂 diary
|   |   📄categories.php
|   |   📄dayrange.php
|   |   📄groupby.php
|   |   📄index.php
|   |   📄login.php
|   |   📄logout.php
|   |   📄sameday.php
|   |   📄search.php
|   |   
|   +---📂 inc
|   |   |   📄_inc1.php
|   |   |   📄_inc2.php
|   |   |   📄_inc3.php
|   |   |   📄_inc_nav.php
|   |   |   📄_validation.php
|   |   |   📄__settings_and_functions.php
|   |   |   
|   |   +---📂 bs-dp
|   |   |   ➡️various files and folders here...
|   |   |           
|   |   +---📂 css
|   |   |       📄styles.css
|   |   |       
|   |   \---📂 images
|   |       \---📂 favicon
|   |               ➡️various files and folders here...
|   |               
|   +---📂 setup
|   |       📄admin-user-create.php
|   |       📄admin-user-password-reset.php
|   |       📄diary-database.sql
|   |       📄diary-database-sample-lorum-data.sql
|   |       
```

## Installation

### Download zip file, unzip it, rename it, and move to server

- [Download the zip file](https://github.com/oedvel/php-mysql-diary/archive/refs/heads/main.zip)
- Unzip the zip file
- The zip file contains the following structure:


```
+---📂 php-mysql-diary-main
|   |   
|   +---📂 php-mysql-diary-main
|   |   |   
|   |   +---📂 files and folders...
|   |   |           
```

- Rename the nested `php-mysql-diary-main` folder (the 2nd folder down from the parent `php-mysql-diary-main` folder) as `diary`
- Place the `diary` folder in your web server's folder (e.g. in `htdocs`) so you have your folder showing in path `C:\Apache24\htdocs\diary` if you're using Windows, for example (I don't have access to a Mac or Linux machine).

### Create the database

- Two files exist to allow you to create the database:
	1. `diary-database.sql` - creates minimal database setup
	2. `diary-database-sample-lorum-data.sql` - creates database containing 10 years' worth of sample diary entries so you can see how the diary functionality works, allowing you to edit entries, try the search, day range, same day, group by etc.
- Run the relevant SQL file using whichever route you use to administer your MySQL database - such as:
	- [MySQL workbench](https://www.mysql.com/products/workbench/)
	- [phpMyAdmin](https://www.phpmyadmin.net/)
	- [SQLyog Community Edition](https://github.com/webyog/sqlyog-community)
	- Command line
- Once you've done that, you should end up with a new database called `diary` containing the following tables:
	- `xx_auth_tokens` - table stores data linked to user logins / authentication. The table is empty to start with.
	- `xx_categories` - table stores categories against which diary dates can be entered. Contains 1 pre-populated category with a name of `Everyday` which can be edited later if required.
	- `xx_days` - table stores diary entries. The table is empty to start with.
	- `xx_users` - table stores user account info. The table is empty to start with.

### Update `settings.php` file

- Edit this file: `inc\__settings_and_functions.php`
	- Set the connection details for your MySQL database, including:
		- `host` (e.g. `localhost` or `127.0.0.1` or something else entirely as per your requirements)
		- `username`
		- `password`
		- `dbname` (name of the database - default value is `diary`)

### Create Admin User

- Navigate to the following URL (replacing `example` with the path for your setup) to create the admin user account:
- `http://example/diary/setup/admin-user-create.php`
- Assuming the previous setup steps (most importantly, creating the database and setting the connection details in the `__settings_and_functions.php` file) have been done, a user account should be created for you when you visit this URL.
- The page will confirm the username and password.
- Make a note of those (ideally in a Password Manager like LastPass, 1Password, NordPass etc), as you will need them for the next step.
- If you lose the password for the admin user, the password can be reset via this page:
- `http://example/diary/setup/admin-user-password-reset.php`
- **⚠️⚠️Delete the `admin-user-password-reset.php` file to remove this option if you are running the site on the public internet⚠️⚠️**

## Accessing the diary website
- Once you have completed the setup, you can hopefully access it via the relevant path in your browser.
- Access it via this URL (replace `example` as per your setup): `http://example/diary/login.php`
- Log in via:
	- username: `diaryadminuser`
	- password: **as per password from earlier setup step**

## Using the diary

### Diary Entry / Edit / Delete Page

- Page: `http://example/diary/index.php`
- Use this page to create and edit entries. Not much more to say really. Entries are associated with a category.
- Recent entries are listed on the right-hand side of the page, or the lower part of the page if accessing the page via mobile.

#### Diary Page Screenshots

![Homepage](https://oedvel.github.io/img/php-mysql-diary/001-index-home.png)

![Edit Diary Entry](https://oedvel.github.io/img/php-mysql-diary/002-index-edit.png)

![Delete Diary Entry](https://oedvel.github.io/img/php-mysql-diary/003-index-delete.png)

### Categories Page

- Page: `http://example/diary/categories.php`
- Use this page to add / edit / delete categories.
- The page lists how many diary entries have been created against each category.
- A diary entry can only be linked to one category at a time, but it is possible to create multiple diary entries for the same day but against different categories. Probably doesn't really make a lot of sense to be able to do that, but that's how it works.
- If a category is in use, it can't be deleted
- Use the blue Lookup button to search for diary entries for that category.

#### Category Page Screenshot

![Categories Page](https://oedvel.github.io/img/php-mysql-diary/004-categories-home.png)

### Group By Page

- Page: `http://example/diary/groupby.php`
- Use this page to search for something in the diary, and see how many times it appeared each year.
- On the search results, click a button with a year value on it to see the entries for the key word for that year.
- **⚠️Supports multi word phrases - e.g. `Fuga  sunt` will return records with those exact two words next to each other, but will not return records with them as separate words where they are not next to each other.**

#### Group By Page Screenshot

![Group By Page](https://oedvel.github.io/img/php-mysql-diary/005-group-by.png)

### Day Range Page

- Page: `http://example/diary/dayrange.php`
- Starting with a set date, search `n` days either side of it.
- Default `Day Range` value is 10.
- Allowed values: 0 to 100
- Click `Hide Action Links` to remove the Edit / Delete etc. links

#### Day Range Page Screenshot

![Group By Page](https://oedvel.github.io/img/php-mysql-diary/006-day-range.png)

### Same Day Page

- Page: `http://example/diary/sameday.php`
- Starting with date in `mm-dd` format, search for other days over the years with the same Day / Month values.
- I find this useful if I want to see - e.g. what I did on a set day over different years - e.g. birthdays, christmas, any other date.

#### Same Day Page Screenshot

![Same Day Page](https://oedvel.github.io/img/php-mysql-diary/007-same-day.png)

### Search Page

- Page: `http://example/diary/search.php`
- Use the search form to search diary entries.
	- A search for `blue cheese` will return diary entries containing `blue` and `cheese` e.g. `The sky is blue` and `I ate some cheese`
	- A search for `blue cheese` with the `Exact Phrase` option ticked will return `I ate blue cheese` but not `The sky is blue` or `I ate some cheese`
	- If you tick `Hide Edit Links` the search results will not include the `/ Edit / Sameday / Day Range / Delete` links next to each diary entry. I find this useful if I want to copy the search results into some other system, so you just get date, category and diary entry and no messy links in the search results.
	- The other fields are pretty self-explanatory.

#### Search Page Screenshot

![Same Day Page](https://oedvel.github.io/img/php-mysql-diary/008-search.png)

### Common links for all diary entries

- For all pages listing diary entries (`index.php`, `dayrange.php`,`sameday.php` and `search.php`), these options exist:
	- `Edit` - edit the diary entry.
	- `Delete` - delete the diary entry.
	- `Single` - view the single diary entry on its own. On doing so, a pagination menu allows you to go through previous / next / first / last diary entries. If a date has multiple diary entries (e.g. 2 diary entries exist, each agains a different category), they are listed on this page too.
	- `Sameday` - search for other diary entries with the same month and day values in MM-DD format.
		- For example, if today is 5th Feb 2024, same day will search a value of `02-05`.
		- Option provided to narrow down by Category.
	- `Day Range` - using the diary entry as the parameter, search for diary entries `n` days either side of the selected date.
		- For example, if today is 5th Feb 2024, the Day Range will search a default of `10` days before and after the selected date.
		- I found I would often want to know what happened either side of a specific date so set up this page.
		- The day range value defaults to 10 but can be anything between 0 and 100.
		- Invalid entries (e.g. non numeric, less than 0, over 100) will return a default value of 10 instead.

### Navigation Links
- The following links appear in the navigation bar:
	- `Home` - the main diary entry page.
	- `Categories` - as detailed in `Categories` heading above.
	- `Group By` - as detailed in `Group By` heading above.
	- `Range` - as detailed in `Range` heading above.
	- `Same Day` - as detailed in `Same Day` heading above.
	- `Search` - as detailed in `Search` heading above.
	- `Logout` - log out of the diary.

## Validation
Each page requiring which you want to secure (e.g. which requires you to be logged in to view it) includes this line at the top of it:

```php
include 'inc/_validation.php';
```

The `_validation.php` page contains various checks to ensure the user is logged in etc. Again, anyone who knows much about PHP and security might look at the methods used and think they're totally crap and useless.

## Data Stuff

Having read all sorts of stuff on Stack Overflow about escaping data, sanitising data, XSS, HTML Purifier etc. I took this approach with this diary project:

- Don't escape user input data that is being saved to database, keep in original form
- PDO parameterised queries handle ensuring user input cannot cause problems with the database
- Use htmlspecialchars to make outputting data to screen safe
- From what I understand, if the diary allowed users to enter HTML and that was saved to the database, and the requirement was to render that saved HTML on the web page as HTML rather than plain-text, then it would be useful to use HTML Purifier to handle outputting that HTML so that nothing nasty was allowed through.
- Things I read along the way:
	- [Is strip_tags() vulnerable to scripting attacks?](https://stackoverflow.com/questions/5788527/is-strip-tags-vulnerable-to-scripting-attacks)
	- [Using HTML Purifier on a site with only plain text input](https://stackoverflow.com/questions/37630774/using-html-purifier-on-a-site-with-only-plain-text-input/)
	- [HTML Purifier convert & -> &amp;](https://stackoverflow.com/questions/40463606/html-purifier-convert-amp)
	- [What are the best PHP input sanitizing functions?](https://stackoverflow.com/questions/3126072/what-are-the-best-php-input-sanitizing-functions)
	- [How can I sanitize user input with PHP?](https://stackoverflow.com/questions/129677/how-can-i-sanitize-user-input-with-php)
	- [(The only proper) PDO tutorial](https://phpdelusions.net/pdo)

## Issues

- If you have any problems with this repository, please [raise a new issue](https://github.com/oedvel/php-mysql-diary/issues/new) or send an email to throwing dot cheese dot github at gmail dot com.
