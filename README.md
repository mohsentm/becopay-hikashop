Docker image for Joomla 3.9 with HikaShop and Becopay payment module
====================


### Change default port

This docker default port is 80. if this port is reserved on your server or wants to use other port, before start the docker you must change the `web service` port on `docker-composer.yml`. e.g. default port `80:80` change to `8080:80`  
```
[...]
services:
  web:
    image: becopay/hikashop
    ports:
      - "8080:80"
	[...]
[...]
```

### Quick start

The easiest way to start joomla with MySQL is using [Docker Compose](https://docs.docker.com/compose/). Just clone this repo and run following command in the root directory. The default `docker-compose.yml` uses MySQL and phpMyAdmin.

```
$ docker-compose up -d
```

For database username and password, please refer to the file `env`. You can also update the file `env` to update those configurations. Below are the default configurations.

```
MYSQL_HOST=db
MYSQL_ROOT_PASSWORD=myrootpassword
MYSQL_USER=jbeco
MYSQL_PASSWORD=jbeco
MYSQL_DATABASE=jbecodb
```

For example, if you want to change the default MYSQL_DATABASE, just update the variable `MYSQL_USER`, e.g. `MYSQL_USER=dbuser`.


## Installation

After starting the container, you'll see the setup page of Joomla. You can use the script `install-joomla` to quickly install Joomla. The installation script uses the variables in the `env` file.

### Joomla

```
$ docker exec -it <container_name> install-joomla
```
### Admin account
Joomla administrator default username and password
```
Username: admin
Password: Admin123456
```
### Database

The default `docker-compose.yml` uses MySQL as the database and starts [phpMyAdmin](https://www.phpmyadmin.net/). The default URL for phpMyAdmin is `http://localhost:8580`. Use MySQL username and password to log in.


## Becopay module configuration

1. Go to __`Joomla's administration panel`__
2. Go to HikaShop's administration panel via __`Components > HikaShop > Configuration`__
3. Go to the menu  __`System > Payment methods`__
4. Click the button __`New`__ and select __`Hikashop Becopay Payment Plugin`__
5. Configure the module
    1. On __`Main information`__ block setup the payment method name and description
    2. On __`Specific configuration`__ block enter your becopay gateway configuration
    	* __Mobile__  - Enter the phone number you registered in the Becopay here.If you don't have Becopay merchat account register [here](https://becopay.com/en/merchant-register/).
		* __Api Base Url__  - Enter Becopay api base url here. If you don't have Becopay merchat account register [here](https://becopay.com/en/merchant-register/).
		* __Api Key__  - Enter your Becopay Api Key here. If you don't have Becopay merchat account register [here](https://becopay.com/en/merchant-register/).
		* __Merchant Currency__ - Enter your money's currency wants to receive.e.g: IRR
		* __Debug__ - If enable it show the all of the error message and useful for debugging
		* __Invalid status__ - Status given to order if has error
		* __Verified status__ - Status given to order after the payment has been completed
6. Publish the payment method
    1. Select the configured payment method in the list of payment methods
	2. Publish it by clicking an icon in the column __`Published`__



## Becopay Support:

* [GitHub Issues](https://github.com/becopay/Hikashop-Becopay-Gateway/issues)
  * Open an issue if you are having issues with this plugin
* [Support](https://becopay.com/en/support/#contact-us)
  * Becopay support
* [Documentation](https://becopay.com/en/io#api)
  * Technical documentation

## Contribute

Would you like to help with this project?  Great!  You don't have to be a developer, either.  If you've found a bug or have an idea for an improvement, please open an [issue](https://github.com/becopay/Hikashop-Becopay-Gateway/issues) and tell us about it.

If you *are* a developer wanting contribute an enhancement, bug fix or other patch to this project, please fork this repository and submit a pull request detailing your changes. We review all PRs!

This open source project is released under the [GPL-3.0](http://www.gnu.org/licenses/gpl-3.0) which means if you would like to use this project's code in your own project you are free to do so.  Speaking of, if you have used our code in a cool new project we would like to hear about it!  [Please send us an email](mailto:io@becopay.com).

## License

Please refer to the [GPL-3.0](http://www.gnu.org/licenses/gpl-3.0) file that came with this project.

