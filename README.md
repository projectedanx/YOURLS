<h1 align="center">
  <a href="https://yourls.org">
    <img src="images/yourls-logo.svg" width=66% alt="YOURLS">
  </a>
</h1>

> Your Own URL Shortener

[![CI](https://github.com/YOURLS/YOURLS/actions/workflows/ci.yml/badge.svg)](https://github.com/YOURLS/YOURLS/actions/workflows/ci.yml) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/YOURLS/YOURLS/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/YOURLS/YOURLS/?branch=master) ![PHP Version Support](https://img.shields.io/packagist/php-v/yourls/yourls) [![Packagist](https://img.shields.io/packagist/v/yourls/yourls.svg)](https://packagist.org/packages/yourls/yourls) [![OpenCollective](https://opencollective.com/yourls/backers/badge.svg)](https://opencollective.com/yourls#contributors)
[![OpenCollective](https://opencollective.com/yourls/sponsors/badge.svg)](#sponsors)

**YOURLS** is a set of PHP scripts that will allow you to run <strong>Y</strong>our <strong>O</strong>wn <strong>URL</strong> <strong>S</strong>hortener, on **your** server. You'll have full control over your data, detailed stats, analytics, plugins, and more. It's free and open-source.

## Table of Contents

- [Getting Started](#getting-started)
- [Setup](#setup)
- [Usage](#usage)
  - [Admin Interface](#admin-interface)
  - [API](#api)
  - [Bookmarklets](#bookmarklets)
- [Architecture](#architecture)
- [Community news, tips and tricks](#community-news-tips-and-tricks)
- [Contributing](#contributing)
- [Backers](#backers)
- [Sponsors](#sponsors)
- [License](#license)

## Getting Started

Check out the complete documentation on [docs.yourls.org](https://docs.yourls.org).
It contains everything from beginners to experts.

## Setup

1.  **Download YOURLS**: Clone or download the YOURLS repository from GitHub.
2.  **Configure your server**: Make sure your server meets the requirements:
    *   PHP 7.2 or greater
    *   MySQL 5.0 or greater
    *   `mod_rewrite` enabled
3.  **Create a database**: Create a new MySQL database and a user with full privileges.
4.  **Configure YOURLS**:
    *   Rename `user/config-sample.php` to `user/config.php`.
    *   Open `user/config.php` and fill in the required database settings.
    *   Customize the other settings to your liking.
5.  **Install YOURLS**: Open your browser and navigate to `http://your-site.com/admin/install.php`.
6.  **Secure your installation**:
    *   Make sure the `user/config.php` file is not publicly accessible.
    *   Change the default username and password in `user/config.php`.
    *   Delete the `admin/install.php` file after the installation is complete.

## Usage

### Admin Interface

The admin interface is located at `http://your-site.com/admin/`. From here, you can:

*   **Shorten URLs**: Add new short URLs and custom keywords.
*   **Manage URLs**: View, edit, and delete existing short URLs.
*   **View stats**: See detailed statistics for each short URL, including clicks, referrers, and geographic location.
*   **Manage plugins**: Activate and deactivate plugins to extend the functionality of YOURLS.
*   **Use tools**: Access the bookmarklets and API information.

### API

YOURLS provides a simple API that allows you to shorten URLs, get stats, and more. The API is located at `http://your-site.com/yourls-api.php`.

For more information on how to use the API, see the [API documentation](https://docs.yourls.org/api.html).

### Bookmarklets

YOURLS comes with handy bookmarklets for easier link shortening and sharing. To use them, drag and drop the links from the **Tools** page in the admin interface to your browser's toolbar.

## Architecture

The YOURLS application is structured as follows:

*   **`/admin`**: Contains the files for the admin interface.
*   **`/includes`**: Contains the core application files, including the functions for handling URLs, the database, and the API.
*   **`/user`**: Contains user-specific files, such as the configuration file and any custom plugins or pages.
*   **`/` (root)**: Contains the main entry points for the application, including the redirection handler, the API, and the admin interface.

## Community news, tips and tricks

* Read and subscribe to the [The Official YOURLS Blog](http://blog.yourls.org)
* Check what the user community makes: plugins, tools, guides and more on [Awesome YOURLS](https://github.com/YOURLS/awesome-yourls)
* Engage users and ask for help in our [community discussions](https://github.com/YOURLS/YOURLS/discussions)
* Keep track of development: "Star" and "Watch" this project, follow [commit messages](https://github.com/YOURLS/YOURLS/commits/master)

## Contributing

Feature suggestion? Bug to report?

__Before opening any issue, please search for existing [issues](https://github.com/YOURLS/YOURLS/issues) (open and closed) and read the [Contributing Guidelines](https://github.com/YOURLS/.github/blob/master/CONTRIBUTING.md).__

## Backers

Do you use and enjoy YOURLS? [Become a backer](https://opencollective.com/yourls#backer) and show your support to our open source project.

[![](https://opencollective.com/yourls/backer/0/avatar.svg)](https://opencollective.com/yourls/backer/0/website)
[![](https://opencollective.com/yourls/backer/1/avatar.svg)](https://opencollective.com/yourls/backer/1/website)
[![](https://opencollective.com/yourls/backer/2/avatar.svg)](https://opencollective.com/yourls/backer/2/website)
[![](https://opencollective.com/yourls/backer/3/avatar.svg)](https://opencollective.com/yourls/backer/3/website)
[![](https://opencollective.com/yourls/backer/4/avatar.svg)](https://opencollective.com/yourls/backer/4/website)
[![](https://opencollective.com/yourls/backer/5/avatar.svg)](https://opencollective.com/yourls/backer/5/website)
[![](https://opencollective.com/yourls/backer/6/avatar.svg)](https://opencollective.com/yourls/backer/6/website)
[![](https://opencollective.com/yourls/backer/7/avatar.svg)](https://opencollective.com/yourls/backer/7/website)
[![](https://opencollective.com/yourls/backer/8/avatar.svg)](https://opencollective.com/yourls/backer/8/website)
[![](https://opencollective.com/yourls/backer/9/avatar.svg)](https://opencollective.com/yourls/backer/9/website)
[![](https://opencollective.com/yourls/backer/10/avatar.svg)](https://opencollective.com/yourls/backer/10/website)
[![](https://opencollective.com/yourls/backer/11/avatar.svg)](https://opencollective.com/yourls/backer/11/website)
[![](https://opencollective.com/yourls/backer/12/avatar.svg)](https://opencollective.com/yourls/backer/12/website)
[![](https://opencollective.com/yourls/backer/13/avatar.svg)](https://opencollective.com/yourls/backer/13/website)
[![](https://opencollective.com/yourls/backer/14/avatar.svg)](https://opencollective.com/yourls/backer/14/website)
[![](https://opencollective.com/yourls/backer/15/avatar.svg)](https://opencollective.com/yourls/backer/15/website)
[![](https://opencollective.com/yourls/backer/16/avatar.svg)](https://opencollective.com/yourls/backer/16/website)
[![](https://opencollective.com/yourls/backer/17/avatar.svg)](https://opencollective.com/yourls/backer/17/website)
[![](https://opencollective.com/yourls/backer/18/avatar.svg)](https://opencollective.com/yourls/backer/18/website)
[![](https://opencollective.com/yourls/backer/19/avatar.svg)](https://opencollective.com/yourls/backer/19/website)
[![](https://opencollective.com/yourls/backer/20/avatar.svg)](https://opencollective.com/yourls/backer/20/website)
[![](https://opencollective.com/yourls/backer/21/avatar.svg)](https://opencollective.com/yourls/backer/21/website)
[![](https://opencollective.com/yourls/backer/22/avatar.svg)](https://opencollective.com/yourls/backer/22/website)
[![](https://opencollective.com/yourls/backer/23/avatar.svg)](https://opencollective.com/yourls/backer/23/website)
[![](https://opencollective.com/yourls/backer/24/avatar.svg)](https://opencollective.com/yourls/backer/24/website)
[![](https://opencollective.com/yourls/backer/25/avatar.svg)](https://opencollective.com/yourls/backer/25/website)
[![](https://opencollective.com/yourls/backer/26/avatar.svg)](https://opencollective.com/yourls/backer/26/website)
[![](https://opencollective.com/yourls/backer/27/avatar.svg)](https://opencollective.com/yourls/backer/27/website)
[![](https://opencollective.com/yourls/backer/28/avatar.svg)](https://opencollective.com/yourls/backer/28/website)
[![](https://opencollective.com/yourls/backer/29/avatar.svg)](https://opencollective.com/yourls/backer/29/website)


## Sponsors

Does your company use YOURLS? Ask your manager or marketing team if your company would be interested in supporting our project. Your company logo will show here. Help support our open-source development efforts by [becoming a sponsor](https://opencollective.com/yourls).

[![](https://opencollective.com/yourls/sponsor/0/avatar.svg)](https://opencollective.com/yourls/sponsor/0/website)
[![](https://opencollective.com/yourls/sponsor/1/avatar.svg)](https://opencollective.com/yourls/sponsor/1/website)
[![](https://opencollective.com/yourls/sponsor/2/avatar.svg)](https://opencollective.com/yourls/sponsor/2/website)
[![](https://opencollective.com/yourls/sponsor/3/avatar.svg)](https://opencollective.com/yourls/sponsor/3/website)
[![](https://opencollective.com/yourls/sponsor/4/avatar.svg)](https://opencollective.com/yourls/sponsor/4/website)
[![](https://opencollective.com/yourls/sponsor/5/avatar.svg)](https://opencollective.com/yourls/sponsor/5/website)
[![](https://opencollective.com/yourls/sponsor/6/avatar.svg)](https://opencollective.com/yourls/sponsor/6/website)
[![](https://opencollective.com/yourls/sponsor/7/avatar.svg)](https://opencollective.com/yourls/sponsor/7/website)
[![](https://opencollective.com/yourls/sponsor/8/avatar.svg)](https://opencollective.com/yourls/sponsor/8/website)
[![](https://opencollective.com/yourls/sponsor/9/avatar.svg)](https://opencollective.com/yourls/sponsor/9/website)
[![](https://opencollective.com/yourls/sponsor/10/avatar.svg)](https://opencollective.com/yourls/sponsor/10/website)
[![](https://opencollective.com/yourls/sponsor/11/avatar.svg)](https://opencollective.com/yourls/sponsor/11/website)
[![](https://opencollective.com/yourls/sponsor/12/avatar.svg)](https://opencollective.com/yourls/sponsor/12/website)
[![](https://opencollective.com/yourls/sponsor/13/avatar.svg)](https://opencollective.com/yourls/sponsor/13/website)
[![](https://opencollective.com/yourls/sponsor/14/avatar.svg)](https://opencollective.com/yourls/sponsor/14/website)
[![](https://opencollective.com/yourls/sponsor/15/avatar.svg)](https://opencollective.com/yourls/sponsor/15/website)
[![](https://opencollective.com/yourls/sponsor/16/avatar.svg)](https://opencollective.com/yourls/sponsor/16/website)
[![](https://opencollective.com/yourls/sponsor/17/avatar.svg)](https://opencollective.com/yourls/sponsor/17/website)
[![](https://opencollective.com/yourls/sponsor/18/avatar.svg)](https://opencollective.com/yourls/sponsor/18/website)
[![](https://opencollective.com/yourls/sponsor/19/avatar.svg)](https://opencollective.com/yourls/sponsor/19/website)


## License

Free software. Do whatever the hell you want with it.
YOURLS is released under the [MIT license](LICENSE).
