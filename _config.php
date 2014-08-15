<?php

/**
 * Default EmailVerifiedMember extends Member class, alter line below if you would only like to extend particular sub-classes.
 */
Member::add_extension('EmailVerifiedMember');

/**
 * Alter the Silverstripe Login Forms
 */
Authenticator::register('EmailVerifiedMemberAuthenticator');
Authenticator::unregister('MemberAuthenticator');

/**
 * Alter the Security Controller 
 */
Security::add_extension('EmailVerifiedSecurity');

// Add Default Records
Page::add_extension('EmailVerifiedMemberDefaultRecords');

// ContentController enthält Methode LoginForm()
// Diese muss im Page_Controller überschrieben werden
//public function LoginForm() {
//  return EmailVerifiedMemberAuthenticator::get_login_form($this);
//}