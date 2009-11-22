Lisphp
======

Lisphp is a Lisp dialect written in PHP. It purposes to be embedded in web
applications to be distributed or web services and so implements sandbox
environment for security issues and multiple environment instances.


Standalone command line interface
---------------------------------

There is `lis.php`, a standalone command line interface. It takes a parameter,
a program filename to execute.

    $ php lis.php program.lisphp

You can run programs in sandbox with an option `-s`.

    $ php lis.php -s program.lisphp


REPL
----

If there is no filename in arguments to `lis.php`, it enters REPL mode.

    $ php lis.php
    >>> (form to evaulate)

Similarly you can specify `-s` to restrict it sandbox.

    $ php -s lis.php

 - `>>>` is a prompt.
 - `==>` is a returned value of evaluation.
 - `!!!` is a thrown exception.


Embed in your app
-----------------

In order to execute the Lisphp program, an _environment instance_ is required.
Environment means global state for the program. It includes global symbols,
built-in functions and macros. A program to execute starts from the initialized
environment. You can initialize an environment with `Lisphp_Environment` class.

    require_once 'Lisphp.php';
    $env = Lisphp_Environment::sandbox();
    $program = new Lisphp_Program($lisphpCode);
    $program->execute($env);

There are two given environment sets in `Lisphp_Environment`. One is the
sandbox, restricted to inside of Lisphp, which is created with the method
`Lisphp_Environment::sandbox()`. It cannot touch outside of Lisphp, PHP side.
Programs cannot access to file system, IO, etc. The other is the full
environment of Lisphp, which is initialized with `Lisphp_Environment::full()`.
In the environment, `use` macro, is for importing native PHP functions and
classes, is provided. File system, IO, socket, et cetera can be accessed in
the full environment. Following code touches file a.txt and write some string.

    (use fopen fwrite fclose)

    {let [fp (fopen "a.txt" "w")]
         (fwrite fp "some string")
         (flose fp)}


About lists and `nil`
---------------------

Lisphp implements lists in primitive, but it has some differences between
original Lisp. In original Lisp, lists are made by [cons][] pairs. But lists in
Lisphp is just instance of `Lisphp_List` class, a subclass of
[`ArrayObject`][arrayobject]. So exactly it is not linked list but similar to
array. In like manner `nil` also is not empty list in Lisphp unlike in original
Lisp. It is a just synonym for PHP [`null`][null] value.


 [cons]: http://en.wikipedia.org/wiki/Cons
 [arrayobject]: http://php.net/manual/en/class.arrayobject.php
 [null]: http://php.net/manual/en/language.types.null.php


Author and license
------------------

The author is Hong, MinHee <http://dahlia.kr/>.

Lisphp is distributed under MIT license.

