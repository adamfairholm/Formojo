# Formojo for MojoMotor v1.0.3

Need advanced forms in MojoMotor? Formojo has you covered. Using a simple tag system that lets you mark up and style your forms however you want, Formojo has all the features you need to create great forms quickly and easily.

## Form Basics

1. You should be able to mark up your forms with whatever HTML you’d like
2. Making forms should be simple but allow you control

With that in mind, Formojo uses a tag syntax to create different types of inputs. Those tags go in your layouts (not in a region).

Here is an example of a very simple form:

	{mojo:formojo:form form_class="signup_form"}
		<p>Name: {name_error} {input:text name="name" label="Name" required="yes"}</p>
		<p>Email: {email_error} {input:email name="email" label="E-mail" required="yes"}</p> <p>{submit}</p>
	{/mojo:formojo:form}

As you can see, we have a tag for each error that takes the form of inputname_error and the input itself. Each input tag starts with input: followed by some parameters that allow you to control the label shown in validation or whether or not the field is required.

You can choose from all sorts of inputs like checkboxes, drop down lists, and even pre-made inputs like a country drop down.

### Basic Form Parameters

Here are the basic form parameters that you can use to change the form. Different functionality options have their own options as well, such as reCAPTCHA.

<table cellpadding="0" cellspacing="0" class="docs_table"> 
 <thead> 
  <tr> 
   <th width="100"> 
    Parameter</th> 
   <th width="100"> 
    Default</th> 
   <th> 
    Description</th> 
  </tr> 
 </thead> 
 <tbody> 
  <tr> 
   <td>form_class</td> 
   <td>site_form</td> 
   <td>Class for the form.</td> 
  </tr> 
  <tr> 
   <td>form_id</td> 
   <td></td> 
   <td>ID for the form.</td> 
  </tr> 
  <tr> 
   <td>return_url</td> 
   <td>current url</td> 
   <td>Allows you to set a page to return to after the form is successfully submitted. The default is just the current page.</td> 
  </tr> 
  <tr> 
   <td>success_message</td> 
   <td>&lt;p class="success"&gt;Form submitted successfully&lt;/p&gt;</td> 
   <td>Allows you to set a success message that will be displayed on the form being successfully submitted via the {success_message} tag.</td> 
  </tr> 
</tbody> 
</table>

## Input Parameters

Each input takes a few parameters that allow you to control how the form behaves. Below is a listing of each of these parameters with their default values:

<table cellpadding="0" cellspacing="0" class="docs_table"> 
 <thead> 
  <tr> 
   <th width="100"> 
    Parameter</th> 
   <th width="100"> 
    Default</th> 
   <th> 
    Description</th> 
  </tr> 
 </thead> 
 <tbody> 
  <tr> 
   <td>required</td> 
   <td>no</td> 
   <td>Setting this to "yes" will make that input required.</td> 
  </tr> 
  <tr> 
   <td>name</td> 
   <td></td> 
   <td>This is a required parameter and is used as the name attribute of the input.</td> 
  </tr> 
  <tr> 
   <td>label</td> 
   <td></td> 
   <td>No required, but helpful. This will be used in error messages if the users submits the form with missing or incorrect information. If this is not supplied, Formojo will try to guess from the name of the input.</td> 
  </tr> 
  <tr> 
   <td>value</td> 
   <td></td> 
   <td>Allows you to set a value attribute for an input. Not needed on inputs like checkboxes and radio. Not required.</td> 
  </tr> 
  <tr> 
   <td>validation</td> 
   <td></td> 
   <td>No required. Allows you to add extra validation to an input by using the validation processes below separated by a pipe character (|). Keep in mind that "required" is set by the required parameter. Take note &#8211; many custom form inputs also come with validation as well.</td> 
  </tr> 
</tbody> 
</table>

### Validation Processes

Below are some validation processes for inputs. Remember, "required" is set by the required="yes/no" parameter. Also, some of these validation items take a parameter is brackets afterwards. Like _matches[password]_.

<table cellpadding="0" cellspacing="0" class="docs_table"> 
 <thead> 
  <tr> 
   <th width="100"> 
    Parameter</th> 
   <th> 
    Description</th> 
  </tr> 
 </thead> 
 <tbody> 
  <tr> 
   <td>matches</td> 
   <td>Makes sure the input matches another input, as passed via brackets. Ex: <strong>matches[password]</strong></td> 
  </tr>
  <tr> 
   <td>min_length</td> 
   <td>Makes sure the input is a minimum length, provided as a parameter. Ex: <strong>min_length<sup id="fnrev42825433651b018f13a8b5" class="footnote"><a href="#fn42825433651b018f13a8b5">12</a></sup></strong></td> 
  </tr> 
  <tr> 
   <td>max_length</td> 
   <td>Same as min_length above but makes sure an input isn&#8217;t longer than a given length.</td> 
  </tr> 
  <tr> 
   <td>exact_length</td> 
   <td>Makes sure the input is an exact length. Ex: <strong>exact_length<sup id="fnrev124122371551b018f13a959" class="footnote"><a href="#fn124122371551b018f13a959">3</a></sup></strong></td> 
  </tr> 
  <tr> 
   <td>alpha</td> 
   <td>Makes sure the input is just alphabetical characters.</td> 
  </tr> 
  <tr> 
   <td>alpha_numeric</td> 
   <td>Makes sure the input is just alphabetical and numerical characters.</td> 
  </tr> 
  <tr> 
   <td>alpha_dash</td> 
   <td>Makes sure the input is just alphabetical characters but allows underscores and dashes.</td> 
  </tr> 
  <tr> 
   <td>numeric</td> 
   <td>Makes sure the input is a number.</td> 
  </tr> 
  <tr> 
   <td>integer</td> 
   <td>Makes sure the input is an integer.</td> 
  </tr> 
  <tr> 
   <td>is_natural</td> 
   <td>Makes sure the input is a natural number.</td> 
  </tr> 
  <tr> 
   <td>is_natural_no_zero</td> 
   <td>Same as is_natural but does not allow zeros.</td> 
  </tr> 
  <tr> 
   <td>valid_email</td> 
   <td>Makes sure the input is a valid email address.</td> 
  </tr> 
  <tr> 
   <td>valid_emails</td> 
   <td>Makes sure the input is a string of valid email addresses separated by commas.</td> 
  </tr> 
</tbody>
</table>

The above validation items are from <a href="http://codeigniter.com/user_guide/libraries/form_validation.html">CodeIgniter&#8217;s Form Validation Library</a>.

## Multi-Value Inputs

Although a text input is pretty simple, something like a list of checkboxes is a little more complicated. Formojo allows you to easily set your dropdown, checkbox, and radio values to create more complex forms.

Here is an example of the syntax for multi-value inputs, using a dropdown of sizes:

	{input:dropdown name="sizes"}
	{option value="Small"}
	{option value="Medium" selected="yes"}
	{option value="Large"}
	{/input:dropdown}

As you can see, multi-value inputs are used as tag pairs, and the options are added in between the pair. You must assign a value to each option, and you can mark an option to be selected by default by adding selected="yes".

## Core Input Types

Although Formojo comes with custom input helpers for things like a drop down list of countries, most forms are built out of the tried and true basic inputs.

<table> 
 <thead> 
  <tr> 
   <th width="100"> 
    Input</th> 
   <th> 
    Description</th> 
  </tr> 
 </thead> 
 <tbody> 
  <tr> 
   <td>text</td> 
   <td>Simple text input.</td> 
  </tr> 
  <tr> 
   <td>textarea</td> 
   <td>Textarea input. Allows you to pass "rows" and "cols" as parameters.</td> 
  </tr> 
  <tr> 
   <td>dropdown</td> 
   <td>Otherwise known as a select element. Creates a drop down list of choices.</td> 
  </tr> 
  <tr> 
   <td>radio</td> 
   <td>Radio button input.</td> 
  </tr> 
  <tr> 
   <td>checkbox</td> 
   <td>Checkbox input.</td> 
  </tr> 
  <tr> 
   <td>yesno_check</td> 
   <td>Allows you to have a simple yes/no checkbox instead of multiple checkboxes.</td> 
  </tr> 
  <tr> 
   <td>hidden</td> 
   <td>A hidden form element. Make sure to set the "value" parameter on this.</td> 
  </tr> 
  <tr> 
   <td>password</td> 
   <td>Hidden typing password input.</td> 
  </tr> 
</tbody> 
</table>

## Setting the Error Markup

Formojo marks up error messages with a simple span tag with a class of "error". For instance:

	<span class="error">Error Message</span>

You can change these values by giving the form tag parameters for pre_error and post_error. These values will then be used in place of the default ones when displaying errors.

## Email Notifications

Note: Always test email notifications. Sending e-mail can be affected by server environments and other factors, so make sure it is working and going to the right people before sending it out to the wild.

Once you have your form set up, you are probably going to want to do something with that data once the users submits it. Formojo gives you the option of sending out two custom email notifications.

Two notifications allows you some flexibility. For instance, if you want to send a nice greeting to someone who has signed up for something, you can do that with notify1. Then with notify2 you can email yourself with the signup details.

### Parameters

The following form parameters are available for email notifications:

<table cellpadding="0" cellspacing="0" class="docs_table"> 
 <thead> 
  <tr> 
   <th width="100"> 
    Parameter</th> 
   <th width="100"> 
    Default</th> 
   <th> 
    Description</th> 
  </tr> 
 </thead> 
 <tbody> 
  <tr> 
   <td>notify1</td> 
   <td></td> 
   <td>The email address (or addresses separated by a pipe "|" character that the notification should go to. This also accepts the name of a field if you want to send the email to the person submitting the form.</td> 
  </tr> 
  <tr> 
   <td>notify1_layout</td> 
   <td></td> 
   <td>The MojoMotor layout to use as the email template. Ex: "email_template"</td> 
  </tr> 
  <tr> 
   <td>notify1_subject</td> 
   <td>Site Name + " Form Submission"</td> 
   <td>The subject of the notification email.</td> 
  </tr> 
  <tr> 
   <td>notify1_from</td> 
   <td></td> 
   <td>The email address (or email/name separated by a pipe "|" character) that the notification should be coming from. Will default to noreply@yourdomain.com if no value provided.</td> 
  </tr> 
</tbody> 
</table>

To get the notify2 parameters, just replace the "1" in the parameters above with a "2".

### Notification Email Template

Formojo uses MojoMotor templates as your email templates for notification, so you can create a custom email notification. All the data from the form is available as tags, identified by their input name:

	{inputname}

So for a form with a name and email input, you could have the following template:

	Dear {name}, Thanks for signing up for our newsletter! We received a sign up request for {email}. -The Team

Inputs that have multiple values can be referred to like this:

	{name} submitted a bug report. They indicated they could contact them via: {contact_methods} – {value} {/contact_methods}

### Extra Template Values

Alongside your form values, you can use these tags in your templates:

<table cellpadding="0" cellspacing="0" class="docs_table"> 
 <thead> 
  <tr> 
   <th width="100">Tag</th> 
   <th> 
    Description</th> 
  </tr> 
 </thead> 
 <tbody> 
  <tr> 
   <td>{when_submitted}</td> 
   <td>The server date and time when the form was submitted.</td> 
  </tr> 
  <tr> 
   <td>&#123;ip_address&#125;</td> 
   <td>The IP address if the submitter.</td> 
  </tr> 
  <tr> 
   <td>{browser}</td> 
   <td>The browser (name + version number) of the submitter.</td> 
  </tr> 
  <tr> 
   <td>{platform}</td> 
   <td>The platform of the submitter.</td> 
  </tr> 
</tbody> 
</table>

## reCAPTCHA

Rather than using a home-grown CAPTCHA system, Formojo offers support for the fantastic reCAPTCHA system. It is free, and really easy to implement.

First, [register your site with reCAPTCHA](http://www.google.com/recaptcha), and grab your **public** and **private** keys. Then, set the following parameters:

<table> 
 <thead> 
  <tr> 
   <th width="100"> 
    Parameter</th> 
   <th width="100"> 
    Default</th> 
   <th> 
    Description</th> 
  </tr> 
 </thead> 
 <tbody> 
  <tr> 
   <td>use_recaptcha</td> 
   <td>no</td> 
   <td>Set this to "yes" to activate reCAPTCHA.</td> 
  </tr> 
  <tr> 
   <td>public_key</td> 
   <td></td> 
   <td>Your public key.</td> 
  </tr> 
  <tr> 
   <td>private_key</td> 
   <td></td> 
   <td>Your private key.</td> 
  </tr> 
  <tr> 
   <td>theme</td> 
   <td>red</td> 
   <td>The reCAPTCHA theme to use. Values can be red, white, blackglass, and clean.</td> 
  </tr> 
</tbody> 
</table>

### Adding reCAPTCHA

After setting the above parameters, simply add in the following tag where you want reCAPTCHA to appear:

	{recaptcha}

### Showing the reCAPTCHA error

Use the following error tag to show a reCAPTCHA error. It works the same as all other errors:

	{recaptcha_error}

## Input Helpers

Very often there are types of inputs that are like core inputs, but more standardized. For instance, if you want a drop down list of countries, it would be very annoying to make a drop down with each of them.

Enter input helpers. Input helpers are short-hand versions of different types of inputs that make making forms easier. In addition to time-saving input helpers, there are input helpers for frequently used inputs that come with their own validation, such as the email input helper.

Input helpers are called exactly like regular inputs, although they may take some extra parameters. In general, though, there is no difference.

	{input:countries required="yes"}

### Bundled Input Helpers

	{countries}

Generates a drop down list of Countries.

	{email}

Generates an HTML5 email input (that degrades to a text input on browsers that don’t use it). Also adds valid_email as validation.

	{timezones}

Generates a drop down list of timezones.

	{random_id}

Generates a random, numerical ID that you can use for various purposes.

## Sample Form

[Sample Form Code](https://gist.github.com/adamfairholm/1311570)
[Sampe Email Code](https://gist.github.com/adamfairholm/1312729)