# VolleyBackend

## Working with the code

This project utilizes [Composer][composer home] for managing PHP dependencies.  If you want to get started quickly,
just perform the following commands:

    $ cd ~/src
    $ git clone git@github.com:BuiltInMenlo/VolleyBackend.git
    $ cd ~/src/VolleyBackend
    $ ./bin/composer-installer.php
    $ ./composer.phar update


## Important notes on Composer

When making changes to this project, keep the following in mind:

- [PHP PEAR][php pear] packages, and versions should be managed through [Composer's][composer home]
  `VolleyBackend/composer.json` file.
- __Never__ commit anything in `VolleyBackend/vendor/`.  Read the following if you have any questions: [Should I
  commit the dependencies in my vendor directory?][composer vendor commit]
- __Do__ commit `composer.lock` (along with `composer.json`) into version control.  Please read: [composer.lock - The
  Lock File][composer composer.lock].
- Run `./composer.phar update` every so often to make sure your local clone is up to date.

Even though the [Composer installation instruction][composer install doc] calls for using `curl` to install
`composer.phar`, __do not__ do it.  Reason being:

- What if _https://getcomposer.org/installer_ goes away, or is unavailable?  Then what?
- It is never good to execute anything you pull directly from the web.
- Version control.  Lets us know exactly which version of the Composer installer was used.

More information on Composer can be found at:

- [Composer home][composer home]
- [Composer documentation][composer doc]


[composer home]: https://getcomposer.org/
[composer doc]: https://getcomposer.org/doc/
[composer install doc]: https://getcomposer.org/doc/01-basic-usage.md#installation
[composer vendor commit]: https://getcomposer.org/doc/faqs/should-i-commit-the-dependencies-in-my-vendor-directory.md
[composer composer.lock]: https://getcomposer.org/doc/01-basic-usage.md#composer-lock-the-lock-file

[php pear]: http://pear.php.net/

