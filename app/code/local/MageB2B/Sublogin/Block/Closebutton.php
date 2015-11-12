<?php
class
MageB2B_Sublogin_Block_Closebutton
extends
Mage_Adminhtml_Block_Widget
{
protected
function
_toHtml()
{
$_e60edee9bbcec9af2084c09a7e3e3bf5a0643e9f
=
'<script>function closeWindow() { if (window.opener) { window.opener.focus(); } window.close(); }setTimeout(closeWindow, 3000);</script>';
$_e60edee9bbcec9af2084c09a7e3e3bf5a0643e9f
.=
'<div class="a-center">
                <button  title="'.$this->__('Close Window').'" type="button" class="scalable "
                onclick="closeWindow()" style=""><span><span><span>'.$this->__('Close Window').'</span></span></span>
                </button></div>
            </div>';
return
$_e60edee9bbcec9af2084c09a7e3e3bf5a0643e9f;
}
}
