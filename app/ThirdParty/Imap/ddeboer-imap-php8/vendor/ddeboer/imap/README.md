# PHP IMAP library

[![Latest Stable Version](http://img.shields.io/packagist/v/ddeboer/imap.svg)](http://packagist.org/packages/ddeboer/imap)
[![Downloads](http://img.shields.io/packagist/dt/ddeboer/imap.svg)](http://packagist.org/packages/ddeboer/imap)
[![Integrate](http://github.com/ddeboer/imap/workflows/CI/badge.svg)](http://github.com/ddeboer/imap/actions)
[![Code Coverage](http://codecov.io/gh/ddeboer/imap/coverage.svg?branch=master)](http://codecov.io/gh/ddeboer/imap?branch=master)

A PHP IMAP library to read and process e-mails over IMAP protocol, built with robust Object-Oriented architecture.

This library requires PHP >= 8.1 with [IMAP](http://www.php.net/manual/en/book.imap.php),
[iconv](http://www.php.net/manual/en/book.iconv.php) and
[Multibyte String](http://www.php.net/manual/en/book.mbstring.php) extensions installed.

## Installation

The recommended way to install the IMAP library is through [Composer](http://getcomposer.org):

```bash
$ composer require ddeboer/imap
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](http://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

## Usage

### Connect and Authenticate

```php
use Ddeboer\Imap\Server;

$server = new Server('imap.gmail.com');

// $connection is instance of \Ddeboer\Imap\Connection
$connection = $server->authenticate('my_username', 'my_password');
```

You can specify port, [flags and parameters](http://secure.php.net/manual/en/function.imap-open.php)
to the server:

```php
$server = new Server(
    $hostname, // required
    $port,     // defaults to '993'
    $flags,    // defaults to '/imap/ssl/validate-cert'
    $parameters
);
```

### Mailboxes

Retrieve mailboxes (also known as mail folders) from the mail server and iterate
over them:

```php
$mailboxes = $connection->getMailboxes();

foreach ($mailboxes as $mailbox) {
    // Skip container-only mailboxes
    // @see http://secure.php.net/manual/en/function.imap-getmailboxes.php
    if ($mailbox->getAttributes() & \LATT_NOSELECT) {
        continue;
    }

    // $mailbox is instance of \Ddeboer\Imap\Mailbox
    printf('Mailbox "%s" has %s messages', $mailbox->getName(), $mailbox->count());
}
```

Or retrieve a specific mailbox:

```php
$mailbox = $connection->getMailbox('INBOX');
```

Delete a mailbox:

```php
$connection->deleteMailbox($mailbox);
```

You can bulk set, or clear, any [flag](http://secure.php.net/manual/en/function.imap-setflag-full.php) of mailbox messages (by UIDs):

```php
$mailbox->setFlag('\\Seen \\Flagged', ['1:5', '7', '9']);
$mailbox->setFlag('\\Seen', '1,3,5,6:8');

$mailbox->clearFlag('\\Flagged', '1,3');
```

**WARNING** You must retrieve new Message instances in case of bulk modify flags to refresh the single Messages flags.

### Messages

Retrieve messages (e-mails) from a mailbox and iterate over them:

```php
$messages = $mailbox->getMessages();

foreach ($messages as $message) {
    // $message is instance of \Ddeboer\Imap\Message
}
```

To insert a new message (that just has been sent) into the Sent mailbox and flag it as seen:

```php
$mailbox = $connection->getMailbox('Sent');
$mailbox->addMessage($messageMIME, '\\Seen');
```

Note that the message should be a string at MIME format (as described in the [RFC2045](http://tools.ietf.org/html/rfc2045)).

#### Searching for Messages

```php
use Ddeboer\Imap\SearchExpression;
use Ddeboer\Imap\Search\Email\To;
use Ddeboer\Imap\Search\Text\Body;

$search = new SearchExpression();
$search->addCondition(new To('me@here.com'));
$search->addCondition(new Body('contents'));

$messages = $mailbox->getMessages($search);
```

**WARNING** We are currently unable to have both spaces _and_ double-quotes
escaped together. Only spaces are currently escaped correctly.
You can use `Ddeboer\Imap\Search\RawExpression` to write the complete search
condition by yourself.

Messages can also be retrieved sorted as per [imap_sort](http://secure.php.net/manual/en/function.imap-sort.php)
function:

```php
$today = new DateTimeImmutable();
$thirtyDaysAgo = $today->sub(new DateInterval('P30D'));

$messages = $mailbox->getMessages(
    new Ddeboer\Imap\Search\Date\Since($thirtyDaysAgo),
    \SORTDATE, // Sort criteria
    true // Descending order
);
```

#### Unknown search criterion: OR

Note that PHP imap library relies on the `c-client` library available at http://www.washington.edu/imap/
which doesn't fully support some IMAP4 search criteria like `OR`. If you want those unsupported criteria,
you need to manually patch the latest version (`imap-2007f` of 23-Jul-2011 at the time of this commit)
and recompile PHP onto your patched `c-client` library.

By the way most of the common search criteria are available and functioning, browse them in `./src/Search`.

References:

1. http://stackoverflow.com/questions/36356715/imap-search-unknown-search-criterion-or
1. imap-2007f.tar.gz: `./src/c-client/mail.c` and `./docs/internal.txt`

#### Message Properties and Operations

Get message number and unique [message id](http://en.wikipedia.org/wiki/Message-ID)
in the form <...>:

```php
$message->getNumber();
$message->getId();
```

Get other message properties:

```php
$message->getSubject();
$message->getFrom();    // Message\EmailAddress
$message->getTo();      // array of Message\EmailAddress
$message->getDate();    // DateTimeImmutable
$message->isAnswered();
$message->isDeleted();
$message->isDraft();
$message->isSeen();
```

Get message headers as a [\Ddeboer\Imap\Message\Headers](/src/Ddeboer/Imap/Message/Headers.php) object:

```php
$message->getHeaders();
```

Get message body as HTML or plain text:

```php
$message->getBodyHtml();    // Content of text/html part, if present
$message->getBodyText();    // Content of text/plain part, if present
```

Reading the message body keeps the message as unseen.
If you want to mark the message as seen:

```php
$message->markAsSeen();
```

Or you can set, or clear, any [flag](http://secure.php.net/manual/en/function.imap-setflag-full.php):

```php
$message->setFlag('\\Seen \\Flagged');
$message->clearFlag('\\Flagged');
```

Move a message to another mailbox:

```php
$mailbox = $connection->getMailbox('another-mailbox');
$message->move($mailbox);
```

Deleting messages:

```php
$mailbox->getMessage(1)->delete();
$mailbox->getMessage(2)->delete();
$connection->expunge();
```

### Message Attachments

Get message attachments (both inline and attached) and iterate over them:

```php
$attachments = $message->getAttachments();

foreach ($attachments as $attachment) {
    // $attachment is instance of \Ddeboer\Imap\Message\Attachment
}
```

Download a message attachment to a local file:

```php
// getDecodedContent() decodes the attachment’s contents automatically:
file_put_contents(
    '/my/local/dir/' . $attachment->getFilename(),
    $attachment->getDecodedContent()
);
```

### Embedded Messages

Check if attachment is embedded message and get it:

```php
$attachments = $message->getAttachments();

foreach ($attachments as $attachment) {
    if ($attachment->isEmbeddedMessage()) {
        $embeddedMessage = $attachment->getEmbeddedMessage();
        // $embeddedMessage is instance of \Ddeboer\Imap\Message\EmbeddedMessage
    }
}
```

An EmbeddedMessage has the same API as a normal Message, apart from flags
and operations like copy, move or delete.

### Timeouts

The IMAP extension provides the [imap_timeout](http://secure.php.net/manual/en/function.imap-timeout.php)
function to adjust the timeout seconds for various operations.

However the extension's implementation doesn't link the functionality to a
specific context or connection, instead they are global. So in order to not
affect functionalities outside this library, we had to choose whether wrap
every `imap_*` call around an optional user-provided timeout or leave this
task to the user.

Because of the heterogeneous world of IMAP servers and the high complexity
burden cost for such a little gain of the former, we chose the latter.

## Mock the library

Mockability is granted by interfaces present for each API.
Dig into [MockabilityTest](tests/MockabilityTest.php) for an example of a
mocked workflow.

## Contributing: run the build locally

Docker is needed to run the build on your computer.

First command you need to run is `make start-imap-server`, which starts an IMAP server locally.

Then the local build can be triggered with a bare `make`.

When you finish the development, stop the local IMAP server with `make stop-imap-server`.
