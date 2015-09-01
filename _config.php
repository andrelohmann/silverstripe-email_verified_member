<?php

if(!class_exists('SapphireTest') || (class_exists('SapphireTest') && !SapphireTest::is_running_test())){
	
	/*
	 * Add The email verified member extensions
	 */
	Member::add_extension('EmailVerifiedMember');
	Security::add_extension('EmailVerifiedSecurity');
	DataObject::add_extension('EmailVerifiedMemberDefaultRecords');

	/**
	 * Alter the Silverstripe Login Forms
	 */
	Authenticator::register('EmailVerifiedMemberAuthenticator');
	Authenticator::unregister('MemberAuthenticator');

	// Validation Domain for the Email validation Link
	Config::inst()->update('Member', 'validation_domain', EMAILVERIFIEDMEMBER_VALIDATION_DOMAIN);
	Config::inst()->update('Member', 'login_after_validation', EMAILVERIFIEDMEMBER_LOGIN_AFTER_VALIDATION);
	// Add all new Users to the Frontend Group
	Config::inst()->update('Member', 'default_add_to_frontend_group', EMAILVERIFIEDMEMBER_ADD_TO_FRONTEND_GROUP);
	// Set the redirect destination Path to use after verification of email was successfull
	Config::inst()->update('Member', 'default_verified_dest', EMAILVERIFIEDMEMBER_DEFAULT_VERIFIED_DESTINATION);

	if(class_exists('BootstrapNavbarLoginForm')){
		Config::inst()->update('BootstrapNavbarLoginForm', 'custom_authenticator_class', "EmailVerifiedMemberAuthenticator");
		Config::inst()->update('BootstrapNavbarLoginForm', 'login_form_class', "EmailVerifiedMemberLoginForm");
	}
}