All Your Stack Posts
====================

WordPress plugin to Grab Questions or Answers from a given user in a given Stack site 
and display them in a simple page ready to print (using your system capabilities).

Inspired by the Meta question [How can I download my content from a beta site?](http://meta.stackoverflow.com/q/194475/185667).

Intended as a mean to export all of a person's participation in a Stack site. When viewing the page, one can print or export as HTML/PDF using the browser and system capabilities.
The maximum number of posts per page is 100 and that's a SE API limitation. 

If the user's Answers are being viewed, the plugin will show 100 Questions per page with the user's Answer bellow it.

If the user's Questions are being viewed, the plugin will show 100 Questions per page with all the Answers given.

**Plugin Meta Box**:  
> ![Plugin meta box](https://raw.github.com/brasofilo/All-My-Stack-Posts/master/includes/screenshot.png)

**Showing Chuck Norris answers**:
> ![Plugin template in action](https://raw.github.com/brasofilo/All-My-Stack-Posts/master/includes/screenshot2.png)

Installation instructions
===========

* After installed and activated, the plugin creates a template in the theme folder.

* Create a new page and select the template "Stack Q&A's".

* The plugin meta box only appears when this template is selected.

* In the plugin's custom meta box, select the Site, User ID, Posts per page (max. 100) and Enable caching.

Acknowledgments
===========

* Copy plugin template to theme folder: [Page Template Example](https://github.com/tommcfarlin/page-template-example/), by Tom McFarlin.

* Some styling rules shameless plugged from: [StackTack](https://github.com/nathan-osman/StackTack-WordPress-Plugin), by Nathan Osman

* Stack Exchange API library: [StackPHP](http://stackapps.com/q/826/10590)

* Pagination scripts: [Zebra Pagination](http://stefangabos.ro/php-libraries/zebra-pagination/), by Stefan Gabos.

* Dropdown with icons: [Image dropdown](https://github.com/marghoobsuleman/ms-Dropdown), by Marghoob Suleman.


Changelog
===========

### 1.0
* Initial Public Release

Credits
===========

This plugin is built and maintained by [Rodolfo Buaiz](http://brasofilo.com), aka brasofilo.

License
===========

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to:

Free Software Foundation, Inc.
51 Franklin Street, Fifth Floor,
Boston, MA
02110-1301, USA.