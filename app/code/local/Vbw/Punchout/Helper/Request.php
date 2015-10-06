<?php



class Vbw_Punchout_Helper_Request
	extends Mage_Core_Helper_Abstract
{

    /**
     *
     * @param \Vbw\Procurement\Punchout\Request $request
     * @return string|null|false
     */
    public function getUserEmailFromRequest ($request)
    {
        try {
            $email = $request->getBody()->getContact()->getEmail();
            if (preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/',$email)) {
                return $email;
            }
            return null;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param \Vbw\Procurement\Punchout\Request $request
     * @return string
     */
    public function getUserNameFromRequest($request)
    {
        try {
            $name = $request->getBody()->getContact()->getName();
            if (empty($name))  {
                $name = $request->getBody()->getShipping()->getShippingTo();
            }
        } catch (Exception $e) {
            // means problem with request.
        }
        return $name;
    }

    public function getUserSplitName ($request)
    {
        $nameArray = array();
        $name = $this->getUserNameFromRequest($request);
        if (empty($name))  {
            $name = "Punchout User";
        }
        preg_match('/^(.+) ([^ ]+)$/',$name,$s);
        if (count($s) > 2) {
            $nameArray[] = $s[1];
            $nameArray[] = $s[2];
        } else {
            $nameArray[] = $name;
            $nameArray[] = " ";
        }
        return $nameArray;
    }

    /**
     *
     * @param \Vbw\Procurement\Punchout\Request $request
     * @return string
     */
    public function getCustomDefaultPunchoutGroup ($request)
    {
        try {
            return $request->getCustom()->getDefaultGroup();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param \Vbw\Procurement\Punchout\Request $request
     * @return string
     */
    public function getCustomDefaultPunchoutUser ($request)
    {
        try {
            return $request->getCustom()->getDefaultUser();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $request
     * @param $node
     * @return mixed
     */
    public function getRequestNode($request,$node)
    {
        $explode = explode("/",$node);
        $current = $request;
        foreach ($explode AS $level) {
            if (is_object($current)) {
                $method = 'get'. ucfirst($level);
                if (method_exists($current,$method)) {
                    $current = $current->$method();
                } else {
                    $current = $current->getData($level);
                }
            }
        }
        return $current;
    }


}
	