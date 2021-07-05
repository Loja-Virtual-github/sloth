# Sloth
## _Adaptation of psr 16 to cache html, css and js files_

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/ea8501b9f7e6440ca4115f68b90b8d6f)](https://www.codacy.com/gh/pablosanches/design-patterns/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=pablosanches/design-patterns&amp;utm_campaign=Badge_Grade)
[![Build Status](https://travis-ci.org/pablosanches/design-patterns.svg?branch=master)](https://travis-ci.org/pablosanches/design-patterns)

## Installation

All you have to do is give a git clone in this repository and run a composer install in the root folder of the project ;).

Install the dependencies and devDependencies and start the server.

    ```sh
        composer require pablosanches/sloth:
    ```

## Usage

```php
    use PabloSanches\Sloth\Cache;
    use PabloSanches\Sloth\CSS;

    $cache = new Cache(CSS::create(array(
        'path' => $this->path,
        'minify' => true
    )));
    $result = $cache->set('style.css', '//-- CSS content here --');
```

### That's it! Now enjoy it! ;)