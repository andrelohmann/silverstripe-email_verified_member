<?php
/**
 * Implements a verification email on user registration
 * @module EmailVerifiedMember
 */
class EmailVerifiedMember extends DataExtension {
    
    private static $deactivate_send_validation_mail = false;
    
    private static $frontend_group_code = 'frontend-members';
    
    private static $frontend_group_title = 'Frontend Members';
    
    private static $frontend_group_permission = 'ACCESS_FRONTEND';
    
    private static $validation_domain = false;
    
    private static $default_add_to_frontend_group = false; // Add every user by default to the frontendgroup
    
    private static $login_after_validation = false;
  
    private static $db = array(
        "Verified" => "Boolean",
        "VerificationString" => "Varchar(32)",
        "VerificationEmailSent" => "Boolean",
    );
    
    private static $defaults = array(
        "Verified" => false
    );
    
    private static $indexes = array(
        'VerificationString' => true
    );

    /**
     * Modify the field set to be displayed in the CMS detail pop-up
     */
    public function updateCMSFields(FieldList $currentFields) {
        $currentFields->insertAfter(new CheckboxField('Verified', 'Email Verified'), "Email");
    }

    /**
     * Additional columns in Member Table displayed in the CMS so that you can easily see whether members email address has been verified etc.
     */
    public function IsVerified() {
        return ($this->owner->Verified)?'Yes':'No';
    }

    public function MemberDateJoined() {
        return $this->owner->dbObject('Created')->Nice();
    }

    public function MemberDateAgoJoined() {
        return $this->owner->dbObject('Created')->Ago();
    }

    public function updateSummaryFields(&$fields) {
        $fields['IsVerified'] = 'EmailIsVerified';
        $fields['MemberDateJoined'] = 'DateMemberJoined';
		$fields['MemberDateAgoJoined'] = 'HowLongAgoMemberJoined';
    }

    /**
     * Check if the user has verified their email address.
     *
     * @param  ValidationResult $result
     * @return ValidationResult
     */
    public function canLogIn(&$result) {
        if (!$this->owner->Verified) {
            $result->error(_t('EmailVerifiedMember.ERRORNOTEMAILVERIFIED', 'Please verify your email address before login.'));
		}
		return $result;
    }


    /**
     * Set VerificationString if not set
     * If not verified log out user and display message.
     */
    public function onBeforeWrite() {
        if (!$this->owner->VerificationString) {
            $this->owner->VerificationString = MD5($this->owner->Email.rand());
		}
		if (!$this->owner->Verified) {
				if ((!$this->owner->VerificationEmailSent)) {
					if(!Config::inst()->get('Member', 'deactivate_send_validation_mail') && Security::default_admin_username() != $this->owner->Email){
						$this->owner->sendemail($this->owner, false);
					}
				}
				if (Member::currentUserID() && ($this->owner->Email == Member::currentUser()->Email)) {
					//parent::onBeforeWrite();
					Member::currentUser()->logOut();

					if (Controller::curr()->redirectedTo() == null) {
						$messageSet = array(
							'default' => _t('EmailVerifiedMember.EMAILVERIFY','Please verify your email address by clicking on the link in the email before logging in.'),
						);

						Session::set("Security.Message.type", 'bad');
						Security::permissionFailure(Controller::curr(), $messageSet);
					}
				}
		}
    }
        
    public function onAfterWrite() {
        //parent::onAfterWrite();
            
        if(Config::inst()->get('Member', 'default_add_to_frontend_group')){
            // Add Member to frontend-members
            $frontendGroup = Group::get()->filter(array("Code" => Config::inst()->get('Member', 'frontend_group_code')))->First();
                
            // FrontendGroup erstellen, wenn noch nicht vorhanden
            if(!$frontendGroup){
                $frontendGroup = new Group();
                $frontendGroup->Code = Config::inst()->get('Member', 'frontend_group_code');
                $frontendGroup->Title = Config::inst()->get('Member', 'frontend_group_title');
                $frontendGroup->write();
                Permission::grant( $frontendGroup->ID, Config::inst()->get('Member', 'frontend_group_permission'));
            }
                
            $frontendGroup->Members()->add($this->owner);
        }
    }

    /**
     * Helper function to send email to member
     *
     * @param Member $member
     * @param Boolean $write Save to database
     */
    public function sendemail($member, $write = true) {
        if($member->Email){

            if(Config::inst()->get('Member', 'validation_domain')) $ValidationLink = Config::inst()->get('Member', 'validation_domain').'/Security/validate/' . urlencode($member->Email) . '/' . $member->VerificationString;
            else $ValidationLink = Director::absoluteBaseURL().'Security/validate/'.urlencode($member->Email).'/'.$member->VerificationString;

            $email = new Email();
            $email->setTemplate('VerificationEmail');
            $email->setTo($member->Email);
            $email->setSubject(_t('EmailVerifiedMember.CONFIRMEMAILSUBJECT', 'Please confirm your email address'));
            $email->populateTemplate(array(
                'ValidationLink' => $ValidationLink,
                'Member' => $member,
            ));
            $member->VerificationEmailSent = $email->send();
            if ($write) $member->write();
        }
    }
    
    // Add the Member to the frontned Group
    public function addToFrontendGroup(){
        $frontendGroup = $frontendGroup = Group::get()->filter(array("Code" => Config::inst()->get('Member', 'frontend_group_code')))->First();
        $frontendGroup->Members()->add($this->owner);
    }
}