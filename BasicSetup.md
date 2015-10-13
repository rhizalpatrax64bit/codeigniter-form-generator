# Basic Setup #

This will help you set up the form generator in Codeigniter

## Initial Setup ##

The first thing you will need to do is download the latest version and copy it into your Codeigniter files.  There are some sample forms that you do not need to copy over.  These are entirely optional:
  * controllers/test\_form.php
  * forms/general.php
  * views/test\_form.php

If you are copying anything that already exists, beware that you may overwrite your existing code.  The files that may be at risk of this are:
  * config/form\_validation.php
  * core/MY\_Loader.php
  * helpers/MY\_form\_helper.php
  * libraries/MY\_Form\_validation.php

## Your First Form ##

We're going to keep the first form simple.  This is based upon the one in _forms/general.php_, but is not identical.  This is a simple login form - you need to get the email address and password of the user and then validate it before logging them in.

The way to think of this is that you are splitting form data from your controller and putting it in it's own separate "Form Controller".  This then will be activated from your controller.  The advantages of separating out the form data is so that don't need to repeat code.  Imagine you have the login form on both a login page and in your page head (like Twitter) - this way, you would only need write the form once.


---


### The Controller ###

Firstly, we create the form controller.  In your application directory, create a folder called **forms** and create a file called "example.php" (it can be anything).

In your controller, you will get the form like this:
```
<?php
class welcome_controller extends CI_Controller {

    public function index() {

        $this->load->form('example');

        /* Note how it assigns the resource as NAME_form */
        $arrForm = $this->example_form->login();

        $this->load->view('login_form', array(
            'form' => $arrForm,
        ));

    }

}
?>
```

This should be broadly familiar to Codeigniter users.  We've loaded the form, queried the method and then sent it to the view file.


---


### The Form Controller ###

We now have to create our form controller.  You have everything at your disposal that you have in a controller (ie, everything) and can load anything in.

```
<?php
class example_form extends MY_Form {

    public function login() {
        /** The form code will go in here **/
    }

}
?>
```

Again, this should be familiar to most people.  Next, we create the form variables in the _login()_ method:
```
$arrForm = array(
    array(
        'name' => 'email_address',
        'type' => 'form_input',
        'label' => 'Email Address',
        'rules' => 'required|valid_email',
    ),
    array(
        'name' => 'password',
        'type' => 'form_password',
        'label' => 'Password',
        'rules' => 'required',
    ),
);
```

This should be fairly straightforward, but as an explanation:
  * **Name** is what becomes the _name_ tag in the HTML;
  * **Type** is the form type (see [Codeigniter Form Helper](http://codeigniter.com/user_guide/helpers/form_helper.html) for a full list)
  * **Label** is what is displayed in the `<label>` tag
  * **Rules** is what validation rules we wish to perform upon this data input.  These are based on the Codeigniter rules, but with additions [Full list](http://code.google.com/p/codeigniter-form-generator/wiki/RuleReference)

The rest of what is in the example can be pretty much copied out
```
/* Set the form */
$this->set_fields($arrForm);

/* Set the buttons */
$this->add_button('submit', 'Login');

/* Activate the form */
if($this->activate()) {

    /**
     * This is where any validation rules that
     * are not part of the default CI things - in
     * this example, this would be where you validate
     * the login credentials in the database.
     */
    
    /* Get the form data */
    $arrData = $this->get_details();

    /* Load stuff */
    $this->load->model('users');
    
    /* Test if login was successful */
    if($this->users->valid_login($arrData['email_address'], $arrData['password'] === false) {
        $this->set_error('valid_login', 'The email address and password combination is invalid. Please try again');
    }
    
    /* Validate the form */
    if($this->run()) {

        /**
         * This is where you would put what happens
         * AFTER we have validated the form. Don't
         * forget to redirect to remove the POST
         * headers
         */
        
        /* Login */

        $this->users->login($arrData['email_address'], $arrData['password']);

        /* Redirect to the login page of the site */
        redirect(current_url());
    }
}

/* Get the form for display */
return $this->get_fields();
```

This will add one button to the action menu - a submit button with the label "Login".  The astute amongst you will notice that this refers to a users model.  This is as an example to show you how this can interact with models.


---


### The View ###

Finally, we can come to the view.  In the controller, we sent this to a file called _login\_form_, so create the file login\_form.php in your views directory.  Because all the hard work has been done in the form controller, we have very little to do here:
```
<h1>Please login below</h1>

<div id="login_form">
    <?php echo output_form(array('fields' => $form)); ?>
</div>
```

All you have to do is run the _output\_form()_ controller with the form fields in 'fields' and you're away (the reason it's an array is because there's lots of commands we can put here and, sometimes, you won't want to even send form - more of this in later posts).

## That's It ##

If you go to that page in a browser, you should now see a splendid, unstyled form that validates how you want it.