Lisphp
======

Lisphp is a Lisp dialect written in PHP. It was created to be embedded in 
web services or to be distributed within web applications. For that reason,
it implements sandbox environment for security issues and multiple 
environment instances.


Requirements
------------

It requires PHP 5.2.5 or higher version. When it works on PHP 5.3 or higher,
it is integrated with lambda functions well. It also requires [SPL][]
which is available and compiled by default in PHP 5.


 [spl]: http://kr.php.net/manual/en/book.spl.php


Standalone command line interface
---------------------------------

There is `lis.php`, a standalone command line interface. It can take one
parameter, the filename of the Lisphp program to be executed.

    $ php lis.php program.lisphp

You can run the program in sandbox with the option `-s`.

    $ php lis.php -s program.lisphp


REPL
----

If there is no filename in arguments to `lis.php`, it enters REPL mode.

    $ php lis.php
    >>> (form to evaulate)

Similarly you can specify `-s` to enter REPL mode in sandbox.

    $ php lis.php -s

 - `>>>` is a prompt.
 - `==>` is a returned value of evaluation.
 - `!!!` is a thrown exception.


Simple tutorial
---------------

    >>> (+ 12 34)
    ==> 46
    >>> (- 1 2)
    ==> -1
    >>> (* 5 6)
    ==> 30
    >>> (/ 30 5)
    ==> 6
    >>> (/ 30 4)
    ==> 7.5
    >>> (% 30 4)
    ==> 2
    >>> (. "hello" "world")
    ==> 'helloworld' 
    >>> (define pi 3.14)
    ==> 3.14
    >>> pi
    ==> 3.14
    >>> (float? pi)
    ==> true
    >>> (string? "abc")
    ==> true
    >>> (* pi 10 10)
    ==> 314


Embed in your app
-----------------

In order to execute a Lisphp program, an _environment instance_ is required.
Environment represents a global state for the program. It includes global
symbols, built-in functions and macros. A program to be executed starts from the
initialized environment. You can initialize the environment with
`Lisphp_Environment` class.

    require_once 'Lisphp.php';
    $env = Lisphp_Environment::sandbox();
    $program = new Lisphp_Program($lisphpCode);
    $program->execute($env);

There are two given environment sets in `Lisphp_Environment`. One is the
sandbox, which is created with the method `Lisphp_Environment::sandbox()`.
In the sandbox mode, programs cannot access the file system, IO, etc. 
The other set is the full environment of Lisphp, which is initialized with
`Lisphp_Environment::full()`. This environment provides `use` macro for
importing native PHP functions and classes. File system, IO, socket, etc.
can be accessed in this full environment. Following code touches file a.txt and
writes some text.

    (use fopen fwrite fclose)

    {let [fp (fopen "a.txt" "w")]
         (fwrite fp "some text")
         (fclose fp)}


Macro `use` and `from`
----------------------

The full environment of Lisphp provides `use` macro. It can import native PHP
functions and classes.

    (use strrev array_sum array-product [substr substring])

It imports by taking function identifiers. Hyphens in identifiers are replaced
by underscores. If you supply a list as an argument to `use` macro, the second
symbol becomes the alias of the first function identifier.

    (strrev "hello")                #=> "olleh"
    (array_sum [array 1 2 3])       #=> 6
    (array-product [array 4 5 6])   #=> 120
    (substring "world" 2)           #=> "rld"

Wrap identifiers with angle brackets in order to import class. According to the
[PEAR naming convention][1] for classes, slashes are treated as hierarchical
separators, so it gets replaced by underscores.

    (use <PDO> Lisphp/<Program>)

Imported classes are applicable and act as instantiating functions.
Static methods in imported classes are also imported as well.

    (<PDO> "mysql:dbname=testdb;host=127.0.0.1" "dbuser" "dbpass")
    (Lisphp/<Program>/load "program.lisphp")

There's also a macro called `from`. It simplifies the step of importing objects
and resolving their names.

    (from Lisphp [<Program> <Scope>])

It has the same behavior as the following code which utilizes `use`.

    (use Lisphp/<Program> Lisphp/<Scope>)
    (define <Program> Lisphp/<Program>)
    (define <Scope> Lisphp/<Scope>)
    (define Lisphp/<Program> nil)
    (define Lisphp/<Scope> nil)


 [1]: http://pear.php.net/manual/en/standards.naming.php


Define custom functions
-----------------------

There is a macro `lambda` that creates a new function. It takes parameter
list as its first argument, and then a trailing function body.

    (lambda (a b) (+ a b))

Functions are also values, so in order to name the function, use `define`.

    (define fibonacci
            {lambda [n]
                    (if (= n 0) 0
                        {if (/= n 1)
                            (+ (fibonacci (- n 1))
                               (fibonacci (- n 2)))
                            1})})

Following code defines the same function.

    (define (fibonacci n)
            (if (= n 0) 0
                {if (/= n 1)
                    (+ (fibonacci (- n 1))
                       (fibonacci (- n 2)))
                    1}))

Function body can contain one or more forms. All forms are evaluated
sequentially then the evaluated value of the last form is returned.

Plus, of course, it implements lexical scope (that is also known as closure)
also.

    (define (adder n)
            {lambda [x]
                    (setf! n (+ n x))
                    n})

Special form `define` defines global variables (and functions),
but `setf!` modifies local variables. See also `let` form.


Define custom macros
--------------------

The built-in macros in Lisphp such as `eval`, `define`, `lambda`, `let`, `if`,
`and`, `or` do not evaluate the form of their arguments'. For example, `define`
takes the name to define as its first argument, but it does not evaluate the 
name. In the same way, `if` takes three forms as arguments, but always
evaluates only two of those arguments and ignores the other. It is impossible
to implement `if` as a function, because then every argument would have to be
evaluated. If you have a case like this, you can try defining a `macro`.

    (define if*
            {macro [let {(cond (eval (car #arguments)
                                     #scope))}
                        (eval (at #arguments (or (and cond 1) 2))
                              #scope)]})

    (define quote*
            [macro (car #arguments)])


Quote
-----

There are two ways to quote a form in Lisphp. First is the macro `quote`, and
the other is quote syntax `:`. (You cannot use the traditional single quotation
because it is already being used as a string literal.)

    (quote abc)
    :abc
    (quote (+ a b))
    :(+ a b)


Playing with objects
--------------------

In order to get an attribute of an object, use `->` macro. It takes an object as
its first argument, and the name of the attribute follows.

    (use [dir <dir>])
    (define directory (<dir> "/tmp"))
    (define handle (-> directory handle))

There is also a syntactic sugar for object attribute chaining.

    (-> object attribute names go here)

This form is equivalent to the following PHP expression.

    $object->attribute->names->go->here

Instance methods can also be invoked by `->`.

    ((-> object method) method arguments)

This form is equivalent to the following PHP expression.

    $object->method($method, $arguments)

Because `->` does not call but gets method as a function object, the expression
above is equivalent to the following code.

    call_user_func(array($object, 'method'), $method, $arguments)



About lists and `nil`
---------------------

Lisphp implements lists in primitive, but it has some differences between
original Lisp. In original Lisp, lists are made from [cons][] pairs. But lists
in Lisphp is just an instance of `Lisphp_List` class, a subclass of
[`ArrayObject`][arrayobject]. So it is not exactly a linked list but is similar
to an array. In the same manner, `nil`is  also not an empty list in Lisphp 
unlike in the original Lisp. It is a just synonym for PHP [`null`][null] value.


 [cons]: http://en.wikipedia.org/wiki/Cons
 [arrayobject]: http://php.net/manual/en/class.arrayobject.php
 [null]: http://php.net/manual/en/language.types.null.php


About value types and reference types
-------------------------------------

In PHP, primitive types such as boolean, integer, float, string, and array
behave as value types. They are always copied when they are passed as arguments
or returned from a called function. For example, `arr` is empty from the
beginning to the end in the following code.

    (define arr (array))
    (set-at! arr "element")

Such behavior is not a problem for scalar types like boolean, integer, float,
and string because they are immutable. Yet this can be problematic for native
arrays.

In PHP, objects behave as reference types, and there exists class `ArrayObject`
which has the same interface as PHP's native array. `Lisphp_List` is a subclass
of `ArrayObject` and you can use these classes instead of arrays.

    (use <ArrayObject>)
    (define arr (<ArrayObject>))
    (set-at! arr "element")
    (define lis (list))
    (set-at! arr "element")


Mailing list
------------

There is the mailing list to discuss about Lisphp: <lisphp@googlegroups.com>.

The web archive for this mailing list: <http://groups.google.com/group/lisphp>.


Author and license
------------------

Lisphp was written by Hong Minhee <http://dahlia.kr/>.

Lisphp is distributed under the MIT license.

