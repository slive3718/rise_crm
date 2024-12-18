# random_compat

[![Build Status](http://travis-ci.org/paragonie/random_compat.svg?branch=master)](http://travis-ci.org/paragonie/random_compat)
[![Scrutinizer](http://scrutinizer-ci.com/g/paragonie/random_compat/badges/quality-score.png?b=master)](http://scrutinizer-ci.com/g/paragonie/random_compat)
[![Latest Stable Version](http://poser.pugx.org/paragonie/random_compat/v/stable)](http://packagist.org/packages/paragonie/random_compat)
[![Latest Unstable Version](http://poser.pugx.org/paragonie/random_compat/v/unstable)](http://packagist.org/packages/paragonie/random_compat)
[![License](http://poser.pugx.org/paragonie/random_compat/license)](http://packagist.org/packages/paragonie/random_compat)
[![Downloads](http://img.shields.io/packagist/dt/paragonie/random_compat.svg)](http://packagist.org/packages/paragonie/random_compat)

PHP 5.x polyfill for `random_bytes()` and `random_int()` created and maintained
by [Paragon Initiative Enterprises](http://paragonie.com).

Although this library *should* function in earlier versions of PHP, we will only
consider issues relevant to [supported PHP versions](http://secure.php.net/supported-versions.php).
**If you are using an unsupported version of PHP, please upgrade as soon as possible.**

## Important

Although this library has been examined by some security experts in the PHP 
community, there will always be a chance that we overlooked something. Please 
ask your favorite trusted hackers to hammer it for implementation errors and
bugs before even thinking about deploying it in production.

**Do not use the master branch, use a [stable release](http://github.com/paragonie/random_compat/releases/latest).**

For the background of this library, please refer to our blog post on 
[Generating Random Integers and Strings in PHP](http://paragonie.com/blog/2015/07/how-safely-generate-random-strings-and-integers-in-php).

### Usability Notice

If PHP cannot safely generate random data, this library will throw an `Exception`.
It will never fall back to insecure random data. If this keeps happening, upgrade
to a newer version of PHP immediately.

## Installing

**With [Composer](http://getcomposer.org):**

    composer require paragonie/random_compat

**Signed PHP Archive:**

As of version 1.2.0, we also ship an ECDSA-signed PHP Archive with each stable 
release on Github.

1. Download [the `.phar`, `.phar.pubkey`, and `.phar.pubkey.asc`](http://github.com/paragonie/random_compat/releases/latest) files.
2. (**Recommended** but not required) Verify the PGP signature of `.phar.pubkey` 
   (contained within the `.asc` file) using the [PGP public key for Paragon Initiative Enterprises](http://paragonie.com/static/gpg-public-key.txt).
3. Extract both `.phar` and `.phar.pubkey` files to the same directory.
4. `require_once "/path/to/random_compat.phar";`
5. When a new version is released, you only need to replace the `.phar` file;
   the `.pubkey` will not change (unless our signing key is ever compromised).

**Manual Installation:**

1. Download [a stable release](http://github.com/paragonie/random_compat/releases/latest).
2. Extract the files into your project.
3. `require_once "/path/to/random_compat/lib/random.php";`

The entrypoint should be **`lib/random.php`** directly, not any of the other files in `/lib`.

## Usage

This library exposes the [CSPRNG functions added in PHP 7](http://secure.php.net/manual/en/ref.csprng.php)
for use in PHP 5 projects. Their behavior should be identical.

### Generate a string of random bytes

```php
try {
    $string = random_bytes(32);
} catch (TypeError $e) {
    // Well, it's an integer, so this IS unexpected.
    die("An unexpected error has occurred"); 
} catch (Error $e) {
    // This is also unexpected because 32 is a reasonable integer.
    die("An unexpected error has occurred");
} catch (Exception $e) {
    // If you get this message, the CSPRNG failed hard.
    die("Could not generate a random string. Is our OS secure?");
}

var_dump(bin2hex($string));
// string(64) "5787c41ae124b3b9363b7825104f8bc8cf27c4c3036573e5f0d4a91ad2eeac6f"
```

### Generate a random integer between two given integers (inclusive)

```php
try {
    $int = random_int(0, 255);
} catch (TypeError $e) {
    // Well, it's an integer, so this IS unexpected.
    die("An unexpected error has occurred"); 
} catch (Error $e) {
    // This is also unexpected because 0 and 255 are both reasonable integers.
    die("An unexpected error has occurred");
} catch (Exception $e) {
    // If you get this message, the CSPRNG failed hard.
    die("Could not generate a random int. Is our OS secure?");
}

var_dump($int);
// int(47)
```

### Exception handling

When handling exceptions and errors you must account for differences between
PHP 5 and PHP7.

The differences:

* Catching `Error` works, so long as it is caught before `Exception`.
* Catching `Exception` has different behavior, without previously catching `Error`.
* There is *no* portable way to catch all errors/exceptions.

#### Our recommendation

**Always** catch `Error` before `Exception`.

#### Example

```php
try {
    return random_int(1, $userInput);
} catch (TypeError $e) {
    // This is okay, so long as `Error` is caught before `Exception`.
    throw new Exception('Please enter a number!');
} catch (Error $e) {
    // This is required, if you do not need to do anything just rethrow.
    throw $e;
} catch (Exception $e) {
    // This is optional and maybe omitted if you do not want to handle errors
    // during generation.
    throw new InternalServerErrorException(
        'Oops, our server is bust and cannot generate any random data.',
        500,
        $e
    );
}
```

### Troubleshooting

#### Exception: "Could not gather sufficient random data"**

If an Exception is thrown, then your operating system is not secure.

1. If you're on Windows, make sure you enable mcrypt.
2. If you're on any other OS, make sure `/dev/urandom` is readable.
   * FreeBSD jails need to expose `/dev/urandom` from the host OS
   * If you use `open_basedir`, make sure `/dev/urandom` is allowed

This library does not (and will not accept any patches to) fall back to
an insecure random number generator.

#### Version Conflict with [Other PHP Project]

If you're using a project that has a line like this in its composer.json

    "require" {
        ...
        "paragonie/random_compat": "~1.1",
        ...
    }

...and then you try to add random_compat 2 (or another library that explicitly
requires random_compat 2, such as [this secure PHP encryption library](http://github.com/defuse/php-encryption)),
you will get a version conflict.

The solution is to get the project to update its requirement string to allow
version 2 and above to be used instead of hard-locking users to version 1.

```diff
"require" {
    ...
-    "paragonie/random_compat": "~1.1",
+    "paragonie/random_compat": "^1|^2",
    ...
}
```

## Contributors

This project would not be anywhere near as excellent as it is today if it 
weren't for the contributions of the following individuals:

* [@AndrewCarterUK (Andrew Carter)](http://github.com/AndrewCarterUK)
* [@asgrim (James Titcumb)](http://github.com/asgrim)
* [@bcremer (Benjamin Cremer)](http://github.com/bcremer)
* [@chriscct7 (Chris Christoff)](http://github.com/chriscct7)
* [@CodesInChaos (Christian Winnerlein)](http://github.com/CodesInChaos)
* [@ConnorVG (Connor S. Parks)](http://github.com/ConnorVG)
* [@cs278 (Chris Smith)](http://github.com/cs278)
* [@cweagans (Cameron Eagans)](http://github.com/cweagans)
* [@dd32 (Dion Hulse)](http://github.com/dd32)
* [@geggleto (Glenn Eggleton)](http://github.com/geggleto)
* [@glensc (Elan Ruusamäe)](http://github.com/glensc)
* [@GrahamCampbell (Graham Campbell)](http://github.com/GrahamCampbell)
* [@ircmaxell (Anthony Ferrara)](http://github.com/ircmaxell)
* [@jdevalk (Joost de Valk)](http://github.com/jdevalk)
* [@jedisct1 (Frank Denis)](http://github.com/jedisct1)
* [@juliangut (Julián Gutiérrez)](http://github.com/juliangut)
* [@kelunik (Niklas Keller)](http://github.com/kelunik)
* [@lt (Leigh)](http://github.com/lt)
* [@MasonM (Mason Malone)](http://github.com/MasonM)
* [@menkaff (Mehran NikNafs)](http://github.com/menkaff)
* [@mmeyer2k (Michael M)](http://github.com/mmeyer2k)
* [@narfbg (Andrey Andreev)](http://github.com/narfbg)
* [@nicolas-grekas (Nicolas Grekas)](http://github.com/nicolas-grekas)
* [@ocean90 (Dominik Schilling)](http://github.com/ocean90)
* [@oittaa](http://github.com/oittaa)
* [@oucil (Kevin Farley)](http://github.com/oucil)
* [@philios33 (Phil Nicholls)](http://github.com/philios33)
* [@redragonx (Stephen Chavez)](http://github.com/redragonx)
* [@relaxnow (Boy Baukema)](http://github.com/relaxnow)
* [@rchouinard (Ryan Chouinard)](http://github.com/rchouinard)
* [@rugk](http://github.com/rugk)
* [@SammyK (Sammy Kaye Powers)](http://github.com/SammyK)
* [@scottchiefbaker (Scott Baker)](http://github.com/scottchiefbaker)
* [@skyosev (Stoyan Kyosev)](http://github.com/skyosev)
* [@sthen (Stuart Henderseon)](http://github.com/sthen)
* [@stof (Christophe Coevoet)](http://github.com/stof)
* [@teohhanhui (Teoh Han Hui)](http://github.com/teohhanhui)
* [@tom-- (Tom Worster)](http://github.com/tom--)
* [@tsyr2ko](http://github.com/tsyr2ko)
* [@trowski (Aaron Piotrowski)](http://github.com/trowski)
* [@twistor (Chris Lepannen)](http://github.com/twistor)
* [@vinkla (Vincent Klaiber)](http://github.com/vinkla)
* [@voku (Lars Moelleken)](http://github.com/voku)
* [@xabbuh (Christian Flothmann)](http://github.com/xabbuh)
