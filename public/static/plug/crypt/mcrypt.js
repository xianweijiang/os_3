/**
 * 说明：PHP7以上，已经不用mcrypt了，基本用openssl代替
 * Created by zxh on 2018/5/10.
 */
var mcrypt_key = '123454536f667445454d537973576562';
var mcrypt_iv = "1234577290ABCDEF1264147890ACAE45";
function encrypt(str) {
    var key = CryptoJS.enc.Utf8.parse(mcrypt_key);
    var iv = CryptoJS.enc.Utf8.parse(mcrypt_iv);
    var encrypted = CryptoJS.AES.encrypt(str, key, {
        iv: iv,
        mode:CryptoJS.mode.CBC,
        padding:CryptoJS.pad.ZeroPadding
    }).toString();
    return encrypted;
}

//解密方法
function decrypt(str) {
    var key = CryptoJS.enc.Utf8.parse(mcrypt_key);
    var iv = CryptoJS.enc.Utf8.parse(mcrypt_iv);
    var decrypted=CryptoJS.AES.decrypt(str,key,{
        iv : iv,
        mode : CryptoJS.mode.CBC,
        padding : CryptoJS.pad.ZeroPadding
    });
    return decrypted.toString(CryptoJS.enc.Utf8);
}