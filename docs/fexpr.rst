Fexprs
======

.. seealso::

   Fexpr_
      Fexpr page in Wikipedia.

   .. _Fexpr: http://en.wikipedia.org/wiki/Fexpr

In Lisphp you can make your own language constructs e.g. ``let``, ``use`` using
fexpr.  Internally :meth:`Lisphp_Applicable::apply` method takes two arguments:
one :class:`Lisphp_Scope` (see :doc:`environment`) and one :class:`Lisphp_List`
which contains it's actual arguments.

Imagine you want to make a special form named ``times``.  It takes one
subexpression to execute and one integer of how many times the subexpression 
would be executed:

.. sourcecode:: cl

   (times (echo "hello") 3)

Note that the subexpression isn't inside ``lambda``.  The first argument
shouldn't be executed (evaluated) before ``(times ...)`` is called.

So :class:`Lisphp_List` passed into :meth:`Lisphp_Applicable::apply` method
doesn't contain arguments that are evaluated into objects, but *not evaluated*
forms.

