<?php 

$id = GETPOST('id');
$sendto = GETPOST('sendto');

if(preg_match("#^(\+33|0)[67][0-9]{8}$#", $sendto)) {
    $tmp = ltrim($sendto, '+33');
    $tmp = ltrim($tmp, '0');
    $num = '+33'.$tmp;
}

?>

<div style="padding:15px;">

<script language="javascript">
    function limitChars(textarea, limit, infodiv)
    {
        var text = textarea.value;
        var textlength = text.length;
        var info = document.getElementById(infodiv);
        
        info.innerHTML = (limit - textlength);
        return true;
    }
</script>

<form method="POST" name="smsform" enctype="multipart/form-data" action="/custom/smsdecanet/admin/smsdecanet.php">
    <input type="hidden" name="token" value="<?= newToken() ?>">
    <input type="hidden" name="action" value="send">
    <input type="hidden" name="models" value="body">
    <input type="hidden" name="smsid" value="0">
    <input type="hidden" name="returnurl" value="/custom/creche/famille_agenda.php?id=<?= $id ?>&action=sendSms">
    <input name="deferred" type="hidden" value="0">
    <input name="priority" type="hidden" value="3">
    <input name="class" type="hidden" value="1">

    <table class="border" width="100%">
        <tbody>
            <tr>
                <td width="180px">Émetteur</td>
                <td><input type="text" name="fromname" size="30" value="Infans"></td>
            </tr>
            <tr>
                <td width="180">Destinataire(s)</td>
                <td><input size="16" id="sendto" name="sendto" value="<?= $num ?>"> (format international ex : +33899701761)</td>
            </tr>
            <tr>
                <td width="180" valign="top">Message</td>
                <td>
                    <textarea cols="40" name="message" id="message" rows="4" onkeyup="limitChars(this, 160, 'charlimitinfospan')">Ceci est un message de test</textarea><div id="charlimitinfo">Nombre de caractères restant: <span id="charlimitinfospan">133</span></div>
                </td>
            </tr>
        </tbody>
    </table>
    <center>
        <input class="button" type="submit" name="sendmail" value="Envoyer SMS"> &nbsp; &nbsp; 
        <input class="button" type="submit" name="cancel" value="Annuler">
    </center>
</form>

</div>