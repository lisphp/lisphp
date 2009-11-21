Lisphp
======

Lisphp is a Lisp dialect written in PHP. It purposes to be embedded in web
applications to be distributed or web services and so implements sandbox
environment for security issues and multiple environment instances.

Embed in your app
-----------------

In order to execute the Lisphp program, an _environment instance_ is required.
Environment means global state for the program. It includes global symbols and
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

