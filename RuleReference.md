# Rule Reference #

Rules that you can use in this library

| **Rule** | **Parameter** | **Description** | **Example** |
|:---------|:--------------|:----------------|:------------|
| required | No            | Returns FALSE if the form element is empty.  If a _form\_dropdown_ element, return FALSE if the value is 0. |             |
| alnum\_space | No            | Returns FALSE if the form element contains anything other than alpha-numeric characters or space. |             |
| alpha\_dash\_space |  No           | Returns FALSE if the form element contains anything other than alpha-numeric characters, underscores, dashes or space. |             |
| alpha\_space | No            | Returns FALSE if the form element contains anything other than alpha characters or space. |             |

You can also use any of the ones that are in the [Codeigniter Form Validation](http://codeigniter.com/user_guide/libraries/form_validation.html#rulereference) library

## Creating a custom rule ##

There will be times when you wish to create your own rule references.  This is very easy to do in your _MY\_Form\_validation.php_ file.

Let's imagine, for example, you want to test that the submitted value is equal to 'value' (why would you? But this is an example).  Create the following function:

```
public function submit_is_value($str) {
    return $str == 'value';
}
```

A simple bit of PHP.  You would activate this with the rule 'submit\_is\_value'.  If it equalled 'value', it would return TRUE and FALSE if not.

Now, let's imagine you want a similar function, but that you want to specify in the form controller what that value is.

```
public function submit_is($str, $value = 'value') {
    return $str == $value;
}
```

Again, a simple PHP test to illustrate the point.  Notice that we now have a $value in here.  To activate this, simply use the rule 'submit\_is`[foobar`]' to test that the submitted value is equal to 'foobar'.  In my example above, it defaults to 'value'.

Remember to add a line in your language file with the same name as the rule.  As in the default CI language, use _%s_ to get the form label name in.

```
$lang['submit_is_value'] = 'The value of the %s field must be value';
```

### A word of caution ###

You can test whatever you like here, but you **MUST** return either TRUE or FALSE.