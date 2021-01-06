
var openssl_IV = '1234567890123412';
var openssl_KEY = '201707eggplant99'
/**
 * 加密
 */
function encrypt(str) {
    var key = CryptoJS.enc.Utf8.parse(openssl_KEY);// 秘钥
    var iv= CryptoJS.enc.Utf8.parse(openssl_IV);//向量iv
    var encrypted = CryptoJS.AES.encrypt(str, key, { iv: iv, mode: CryptoJS.mode.CBC, padding: CryptoJS.pad.Pkcs7});
    return encrypted.toString();
}
/**
 * 解密
 * @param str
 */
function decrypt(str) {
    var key = CryptoJS.enc.Utf8.parse(openssl_KEY);// 秘钥
    var iv=    CryptoJS.enc.Utf8.parse(openssl_IV);//向量iv
    var decrypted = CryptoJS.AES.decrypt(str,key,{iv:iv,padding:CryptoJS.pad.Pkcs7});
    return decrypted.toString(CryptoJS.enc.Utf8);
}