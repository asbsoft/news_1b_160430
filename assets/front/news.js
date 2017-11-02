
function utcToLocalDatetime(utctime)
{
    var now = new Date();
    var tzoffset = now.getTimezoneOffset();
    var utime = utctime - (60 * tzoffset); //sec
    now.setTime(1000 * utime); //ms
    return result = now.toLocaleString();
}
