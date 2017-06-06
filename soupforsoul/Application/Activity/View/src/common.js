/**
 * Created by ljp on 2016/8/5.
 */

var G_phone;
var G_login_flag = false;
var G_prefix = "aoyun";

function checkLogin() {
    var phone = localStorage.getItem(G_prefix + "_phone");
    if (phone) {
        G_phone = phone;
        G_login_flag = true;
    }
}

function savePhone(phone) {
    localStorage.setItem(G_prefix + "_phone", phone);
    G_login_flag = true;
}