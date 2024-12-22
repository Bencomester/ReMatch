<?php
function languageDropdown($chooseLanguage, $lang) : string
{
    $en = $lang == 'en' ? 'selected' : '';
    $hu = $lang == 'hu' ? 'selected' : '';
return <<<EOD
<div class="language-dropdown">
    <form class="language-dropdown-form" method="get" action="">
        <label for="language">$chooseLanguage:</label>
        <select name="lang" id="language" onchange="this.form.submit()">
            <option value="en" $en>English</option>
            <option value="hu" $hu>Magyar</option>
        </select>
    </form>
</div>
EOD;}
