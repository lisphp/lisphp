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
forms.  That means, you can ignore some of them, or evaluated some of them
multiple times as well:

.. sourcecode:: php

   <?php

   class Times implements Lisphp_Applicable
   {
       public function apply(Lisphp_Scope $scope, Lisphp_List $args)
       {
           if (count($args) != 2) {
               throw new InvalidArgumentException('times takes two arguments');
           }
           $times = $args[1]->evaluate($scope);
           for ($i = 0; $i < $times; ++$i) {
               $result = $args[0]->evaluate($scope);
           }
           return $result;
       }
   }


Interfaces
----------

.. interface:: Lisphp_Applicable

   Lisphp's fexpr interface.

   .. method:: apply($scope, $args)

      :param $scope: the scope which called this
      :type $scope: :class:`Lisphp_Scope`
      :param $args: the list of arguments *not evaluated yet*.  these are
                    forms
      :type $args: :class:`Lisphp_List`
      :returns: the return value

