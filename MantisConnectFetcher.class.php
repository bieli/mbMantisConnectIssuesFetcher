<?php

/**
 * Class for fetch oll issues and attachments from Mantis by MantisCOnnecto API
 *
 */
class MantisConnectFetcher
{
    const STORAGE_TYPE__FILES = 1;
    const STORAGE_DIR = './mantis_data/';

    private $lowIssueId  = -2;
    private $highIssueId = -1;
    private $mantisConnectWsdlUrl = '';
    private $soapClientObj = null;
    private $mantisUserLogin  = '';
    private $mantisUserPassword = '';
    private $storageType = self::STORAGE_TYPE__FILES;


    public function __construct($i_MantisConnectWsdlUrl = null)
    {
        if ( null !== $i_MantisConnectWsdlUrl )
        {
            $this->setMantisConnectWsdlUrl($i_MantisConnectWsdlUrl);
        }
    }

    public function setMantisConnectWsdlUrl($i_MantisConnectWsdlUrl)
    {
        $this->mantisConnectWsdlUrl = $i_MantisConnectWsdlUrl;

        return true;
    }

    public function setAuth($i_MantisUserLogin, $i_MantisUserPassword)
    {
        $this->mantisUserLogin = $i_MantisUserLogin;
        $this->mantisUserPassword = $i_MantisUserPassword;

        return true;
    }

    public function setLowIssueId($i_LowIssueId)
    {
        $this->lowIssueId = $i_LowIssueId;

        return true;
    }

    public function setHighIssueId($i_HighIssueId)
    {
        $this->highIssueId = $i_HighIssueId;

        return true;
    }

    public function setStorageType($i_StorageType)
    {
        $this->storageType = $i_StorageType;

        return true;
    }

    public function init()
    {
        $this->soapClientObj = new SoapClient($this->mantisConnectWsdlUrl);
    }

    public function deInit()
    {
        unset($this->soapClientObj);
    }

    public function proccess()
    {
        if ( false === file_exists(self::STORAGE_DIR) )
        {
            mkdir(self::STORAGE_DIR);
        }

        $_itemsCount = $this->highIssueId - $this->lowIssueId;
        $_gaugeSize  = 10;
        $_gaugeLen   = (integer) $_itemsCount / $_gaugeSize;
    
        echo "\n|" . str_repeat('_', $_gaugeSize) . "|\r|";

        for ( $_issueId = $this->lowIssueId; $_issueId <= $this->highIssueId; $_issueId++ )
        {
            try
            {
                $_result = $this->soapClientObj->mc_issue_exists($this->mantisUserLogin, $this->mantisUserPassword, $_issueId);
                if ( false === $_result )
                {
                    continue;
                }

                $_results = $this->soapClientObj->mc_issue_get($this->mantisUserLogin, $this->mantisUserPassword, $_issueId);
                $_results = objectToArray($_results);

                // make subdir for 
                if ( false === file_exists(self::STORAGE_DIR . $_issueId) )
                {
                    mkdir(self::STORAGE_DIR . $_issueId);
                }

// var_dump(objectToArray($_results));

                if ( true === isset($_results['attachments']) )
                {
                    foreach ( $_results['attachments'] as $_key => $_value )
                    {
                        $_attachmentContent = $this->soapClientObj->mc_issue_attachment_get($this->mantisUserLogin, $this->mantisUserPassword, $_value['id']);
                        if ( false !== strpos($_value['filename'], '/') )
                        {
                            $_value['filename'] = str_replace('/', '_', $_value['filename']);
                        }

                        file_put_contents(self::STORAGE_DIR . $_issueId . '/' . $_value['filename'], 
                            $_attachmentContent);
                    }
                }

//            $_resultsIssueData    = serialize(objectToArray($_results));
                $_resultsIssueData    = objectToArray($_results);
//                $_resultsIssueDataMd5 = md5($_resultsIssueData);

                if ( false === file_exists(self::STORAGE_DIR . $_issueId . '.issue') )
                {
                    file_put_contents(self::STORAGE_DIR . $_issueId . '/' . $_issueId . '.issue', var_export($_resultsIssueData, true));
                }
                else
                {
//FIXME: add special procedure in this situaltions
//                move(self::STORAGE_DIR . $_issueId . '/' . $_issueId, 
//                     self::STORAGE_DIR . $_issueId . '/' . $_issueId . '.' . $_resultsIssueDataMd5);
                }
            }
            catch ( SoapFault $_sf )
            {
                continue;
            }

            if ( 0 === ( $_issueId % ($_gaugeLen) ) )
            {
//                echo "=";
                flush();
            }
echo $_issueId . "\n";
//            sleep(1);
        }
        echo "|\n\n";
    }    

    /**
     * Convert an object to an array
     *
     * @param    object  $object The object to convert
     * @reeturn  array
     * @static
     */
    private function objectToArray($i_Object)
    {
        if( !is_object( $i_Object ) && !is_array( $i_Object ) )
        {
            return $i_Object;
        }

        if( is_object( $i_Object ) )
        {
            $i_Object = get_object_vars( $i_Object );
        }

        return array_map( array($this, 'objectToArray'), $i_Object);
    }
}

