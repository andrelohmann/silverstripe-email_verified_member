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
DataObject::add_extension('EmailVerifiedMemberDefaultRecords');

// Validation Domain for the Email validation Link
EmailVerifiedMember::set_validation_domain(EMAILVERIFIEDMEMBER_VALIDATION_DOMAIN);
EmailVerifiedMember::set_login_after_validation(EMAILVERIFIEDMEMBER_LOGIN_AFTER_VALIDATION);
// Add all new Users to the Frontend Group
EmailVerifiedMember::set_default_add_to_frontend_group(EMAILVERIFIEDMEMBER_ADD_TO_FRONTEND_GROUP);
// Set the redirect destination Path to use after verification of email was successfull
EmailVerifiedSecurity::set_default_verified_dest(EMAILVERIFIEDMEMBER_DEFAULT_VERIFIED_DESTINATION);

if(!class_exists('BootstrapNavbarLoginForm')){
    BootstrapNavbarLoginForm::set_AuthenticatorClass("EmailVerifiedMemberAuthenticator");
    BootstrapNavbarLoginForm::set_LoginFormClass("EmailVerifiedMemberLoginForm");
}
