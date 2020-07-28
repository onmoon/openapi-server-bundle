# Contributing
- Any contribution must provide tests for additional introduced conditions
- Any un-confirmed issue needs a failing test case before being accepted
- Pull requests must be sent from a new hotfix/feature branch, not from `master`.
- Pull request branches must start from the latest commit on the master branch
- If you start working on an existing issue, please leave a comment in the issue that you are working on it
- Pull request description should reference the issue it closes e.g. `Closes #30`
- All CI checks should pass for the pull request to be accepted. If you have an unresolvable
issue with any of the CI checks (psalm/phpstan/phpcs/infection) and want to propose to ignore the error, describe
your proposal in the pull request for the according CI error.

## Installation
To install the project and run the tests, you need to clone it first:
```sh
$ git clone git@github.com:onmoon/openapi-server-bundle.git
```

You will then need to run a [Composer](https://getcomposer.org/) installation:

```sh
$ cd openapi-server-bundle
$ curl -s https://getcomposer.org/installer | php
$ php composer.phar update
```

## Testing
The PHPUnit version to be used is the one installed as a dev- dependency via composer:

```sh
$ vendor/bin/phpunit
```

Please ensure all new features or conditions are covered by unit tests.
