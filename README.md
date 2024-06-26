# Simple PHP URL shortener tool

## Description
This project is a simple URL shortener built with PHP and MySQL. It allows users to enter a long URL, and it generates a shortened version that is easier to share.


## Features
- **Short URL Generation**: Generates a short code for the URL, stores it, and provides a clickable link to the shortened URL.
- **Responsive Design**: The interface is designed to be mobile-friendly and responsive, adapting to various device sizes.


## Installation

### Prerequisites
- PHP 7.4 or higher
- Apache Web Server with mod_rewrite enabled
- Composer for managing dependencies
- MySQL database for original links and short link codes

### Setup
1. Clone the repository to your local machine or download the zip and extract it.
2. Navigate to the root directory of the project.
3. Open index.php go to line 25 and enter your domain name
4. Database Setup: Create a MySQL database with table called urls with fields
  `short_code` varchar(64),
  `long_url` varchar(255)
5. Environment Variables: Make sure all the necessary environment variables are set in the .env file


## Usage
- Enter required details to to the .env file
- Start the server

## License
 [GPL-3.0 license](https://github.com/Luhte/php-url-shortener/LICENSE.md) 

## Authors
- **Luhte** - _Initial work_ - [Luhte](https://github.com/luhte)

## Acknowledgments
- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv)
  

![](https://komarev.com/ghpvc/?username=luhte&style=for-the-badge&label=Repository+Views)
