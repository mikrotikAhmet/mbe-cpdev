<?php


class Vbw_Punchout_Model_Resource_Setup
    extends Vbw_Punchout_Model_Resource_Setup_Cli
{

    /**
     * run main process
     *
     * @return Vbw_Punchout_Model_Resource_Setup
     */
    public function run()
    {
        $this->runCategoryUnspscField();
        $this->runCategoryExportField();
        return $this;
    }

    public function runCategoryUnspscField ()
    {
        // check code.
        if (false !== $code = $this->checkCategoryUnspc()) {
            // code already exists.
        } else {
            // make code.
            if (false != $code = $this->createCategoryUnspsc()) {
                // cool, things are good.
            } else {
                // not good.
            }
        }
        if (is_numeric($code)) {
            // check placement.
            if (false != $data = $this->checkCategoryUnspscPlacement($code)) {
                // already there, good.
            } else {
                // no placement
                if (false != $data = $this->createCategoryUnspscPlacement($code)) {
                    // placement made.
                } else {
                    // no placement, bad
                }
            }
        }
    }

    public function runCategoryExportField ()
    {
        if (false !== $code = $this->checkCategoryExport()) {
            // code exists.
        } else {
            // no code, make it
            if (false != $code = $this->createCategoryExport()) {
                // field was created!
            } else {
                // arg..
            }
        }
        if (is_numeric($code)) {
            // checking placement
            if (false != $data = $this->checkCategoryExportPlacement($code)) {
                // okay.
            } else {
                // adding placement
                if (false != $data = $this->createCategoryExportPlacement($code)) {
                    // placement created.
                } else {
                    // no placement.. arg
                }
            }
        }
    }

}