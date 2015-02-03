<?php

/**
 * _config.php
 * Object::add_extension('Page','RequireDefaults');
 * SiteTree::set_create_default_pages(false);
 * 
 * http://www.sspaste.com/paste/show/4c56a06978bf7
 */

class EmailVerifiedMemberDefaultRecords extends DataExtension{
    
    // Set Default Frontend group for new members
    public function requireDefaultRecords() {
        parent::requireDefaultRecords();
    
        // Add default FrontendMember group if none with permission code ADMIN exists
        $frontendGroup = Permission::get_groups_by_permission(EmailVerifiedMember::get_frontend_group_permission());
	if(!defined('CreateFrontendGroupOnce') && !$frontendGroup->count()){
            define('CreateFrontendGroupOnce', true);
            $frontendGroup = new Group();
            $frontendGroup->Code = EmailVerifiedMember::get_frontend_group_code();
            $frontendGroup->Title = EmailVerifiedMember::get_frontend_group_title();
            $frontendGroup->write();
            Permission::grant( $frontendGroup->ID, EmailVerifiedMember::get_frontend_group_permission());
            DB::alteration_message('Frontend Members group created',"created");
        }
        
        // DefaultAdmin validation mail validated setzen
        // If-Block darf nur einmal durchlaufen werden
        if (!defined('VerifyAdminOnce')){
            define('VerifyAdminOnce', true);
            EmailVerifiedMember::set_deactivate_send_validation_mail(true);
            $Admin = Security::findAnAdministrator();
            EmailVerifiedMember::set_deactivate_send_validation_mail(false);
            $Admin->Verified = true;
            $Admin->VerificationEmailSent = true;
            EmailVerifiedMember::set_deactivate_send_validation_mail(true);
            $Admin->write();
            EmailVerifiedMember::set_deactivate_send_validation_mail(false);
            DB::alteration_message('Admin email verified', 'created');
        }
    }
}