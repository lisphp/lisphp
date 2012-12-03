CHANGELOG
=========

* 1.0.1 (2012-xx-xx)

  * Make `use` and `from` more PHP 5.3 friendly.

    `(use Foo/<Bar>)` no longer works, `from` and namespaces should be used
    instead.

    `(from Foo <Bar> <Baz>)` now loads `Foo\Bar` and `Foo\Baz` instead of
    `Foo_Bar` and `Foo_Baz`.

  * Bugfix: Allow omitting else clause of an if form.

* 1.0.0 (2012-12-01)

  * First tagged release.
