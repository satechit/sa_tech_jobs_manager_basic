"use strict";
var reSpace = '[ \\t]+';
var reSpaceOpt = '[ \\t]*';
var reMeridian = '(?:([ap])\\.?m\\.?([\\t ]|$))';
var reHour24 = '(2[0-4]|[01]?[0-9])';
var reHour24lz = '([01][0-9]|2[0-4])';
var reHour12 = '(0?[1-9]|1[0-2])';
var reMinute = '([0-5]?[0-9])';
var reMinutelz = '([0-5][0-9])';
var reSecond = '(60|[0-5]?[0-9])';
var reSecondlz = '(60|[0-5][0-9])';
var reFrac = '(?:\\.([0-9]+))';
var reDayfull = 'sunday|monday|tuesday|wednesday|thursday|friday|saturday';
var reDayabbr = 'sun|mon|tue|wed|thu|fri|sat';
var reDaytext = reDayfull + '|' + reDayabbr + '|weekdays?';
var reReltextnumber = 'first|second|third|fourth|fifth|sixth|seventh|eighth?|ninth|tenth|eleventh|twelfth';
var reReltexttext = 'next|last|previous|this';
var reReltextunit = '(?:second|sec|minute|min|hour|day|fortnight|forthnight|month|year)s?|weeks|' + reDaytext;
var reYear = '([0-9]{1,4})';
var reYear2 = '([0-9]{2})';
var reYear4 = '([0-9]{4})';
var reYear4withSign = '([+-]?[0-9]{4})';
var reMonth = '(1[0-2]|0?[0-9])';
var reMonthlz = '(0[0-9]|1[0-2])';
var reDay = '(?:(3[01]|[0-2]?[0-9])(?:st|nd|rd|th)?)';
var reDaylz = '(0[0-9]|[1-2][0-9]|3[01])';
var reMonthFull = 'january|february|march|april|may|june|july|august|september|october|november|december';
var reMonthAbbr = 'jan|feb|mar|apr|may|jun|jul|aug|sept?|oct|nov|dec';
var reMonthroman = 'i[vx]|vi{0,3}|xi{0,2}|i{1,3}';
var reMonthText = '(' + reMonthFull + '|' + reMonthAbbr + '|' + reMonthroman + ')';
var reTzCorrection = '((?:GMT)?([+-])' + reHour24 + ':?' + reMinute + '?)';
var reDayOfYear = '(00[1-9]|0[1-9][0-9]|[12][0-9][0-9]|3[0-5][0-9]|36[0-6])';
var reWeekOfYear = '(0[1-9]|[1-4][0-9]|5[0-3])';
function satechjobs_processMeridian(hour, meridian) {
    meridian = meridian && meridian.toLowerCase();
    switch (meridian) {
        case 'a':
            hour += hour === 12 ? -12 : 0;
            break;
        case 'p':
            hour += hour !== 12 ? 12 : 0;
            break;
    }
    return hour;
}
function processYear(yearStr) {
    var year = +yearStr;
    if (yearStr.length < 4 && year < 100) {
        year += year < 70 ? 2000 : 1900;
    }
    return year;
}
function lookupMonth(monthStr) {
    return {
        jan: 0,
        january: 0,
        i: 0,
        feb: 1,
        february: 1,
        ii: 1,
        mar: 2,
        march: 2,
        iii: 2,
        apr: 3,
        april: 3,
        iv: 3,
        may: 4,
        v: 4,
        jun: 5,
        june: 5,
        vi: 5,
        jul: 6,
        july: 6,
        vii: 6,
        aug: 7,
        august: 7,
        viii: 7,
        sep: 8,
        sept: 8,
        september: 8,
        ix: 8,
        oct: 9,
        october: 9,
        x: 9,
        nov: 10,
        november: 10,
        xi: 10,
        dec: 11,
        december: 11,
        xii: 11
    }[monthStr.toLowerCase()];
}
function lookupWeekday(dayStr, desiredSundayNumber) {
    if (desiredSundayNumber === void 0) { desiredSundayNumber = 0; }
    var dayNumbers = {
        mon: 1,
        monday: 1,
        tue: 2,
        tuesday: 2,
        wed: 3,
        wednesday: 3,
        thu: 4,
        thursday: 4,
        fri: 5,
        friday: 5,
        sat: 6,
        saturday: 6,
        sun: 0,
        sunday: 0
    };
    return dayNumbers[dayStr.toLowerCase()] || desiredSundayNumber;
}
function lookupRelative(relText) {
    var relativeNumbers = {
        last: -1,
        previous: -1,
        this: 0,
        first: 1,
        next: 1,
        second: 2,
        third: 3,
        fourth: 4,
        fifth: 5,
        sixth: 6,
        seventh: 7,
        eight: 8,
        eighth: 8,
        ninth: 9,
        tenth: 10,
        eleventh: 11,
        twelfth: 12
    };
    var relativeBehavior = {
        this: 1
    };
    var relTextLower = relText.toLowerCase();
    return {
        amount: relativeNumbers[relTextLower],
        behavior: relativeBehavior[relTextLower] || 0
    };
}
function processTzCorrection(tzOffset, oldValue) {
    if (oldValue === void 0) { oldValue = null; }
    var reTzCorrectionLoose = /(?:GMT)?([+-])(\d+)(:?)(\d{0,2})/i;
    tzOffset = tzOffset && tzOffset.match(reTzCorrectionLoose);
    if (!tzOffset) {
        return oldValue;
    }
    var sign = tzOffset[1] === '-' ? 1 : -1;
    var hours = +tzOffset[2];
    var minutes = +tzOffset[4];
    if (!tzOffset[4] && !tzOffset[3]) {
        minutes = Math.floor(hours % 100);
        hours = Math.floor(hours / 100);
    }
    return sign * (hours * 60 + minutes);
}
var formats = {
    yesterday: {
        regex: /^yesterday/i,
        name: 'yesterday',
        callback: function () {
            this.rd -= 1;
            return this.resetTime();
        }
    },
    now: {
        regex: /^now/i,
        name: 'now'
    },
    noon: {
        regex: /^noon/i,
        name: 'noon',
        callback: function () {
            return this.resetTime() && this.time(12, 0, 0, 0);
        }
    },
    midnightOrToday: {
        regex: /^(midnight|today)/i,
        name: 'midnight | today',
        callback: function () {
            return this.resetTime();
        }
    },
    tomorrow: {
        regex: /^tomorrow/i,
        name: 'tomorrow',
        callback: function () {
            this.rd += 1;
            return this.resetTime();
        }
    },
    timestamp: {
        regex: /^@(-?\d+)/i,
        name: 'timestamp',
        callback: function (match, timestamp) {
            this.rs += +timestamp;
            this.y = 1970;
            this.m = 0;
            this.d = 1;
            this.dates = 0;
            return this.resetTime() && this.zone(0);
        }
    },
    firstOrLastDay: {
        regex: /^(first|last) day of/i,
        name: 'firstdayof | lastdayof',
        callback: function (match, day) {
            if (day.toLowerCase() === 'first') {
                this.firstOrLastDayOfMonth = 1;
            }
            else {
                this.firstOrLastDayOfMonth = -1;
            }
        }
    },
    backOrFrontOf: {
        regex: RegExp('^(back|front) of ' + reHour24 + reSpaceOpt + reMeridian + '?', 'i'),
        name: 'backof | frontof',
        callback: function (match, side, hours, meridian) {
            var back = side.toLowerCase() === 'back';
            var hour = +hours;
            var minute = 15;
            if (!back) {
                hour -= 1;
                minute = 45;
            }
            hour = satechjobs_processMeridian(hour, meridian);
            return this.resetTime() && this.time(hour, minute, 0, 0);
        }
    },
    weekdayOf: {
        regex: RegExp('^(' + reReltextnumber + '|' + reReltexttext + ')' + reSpace + '(' + reDayfull + '|' + reDayabbr + ')' + reSpace + 'of', 'i'),
        name: 'weekdayof'
    },
    mssqltime: {
        regex: RegExp('^' + reHour12 + ':' + reMinutelz + ':' + reSecondlz + '[:.]([0-9]+)' + reMeridian, 'i'),
        name: 'mssqltime',
        callback: function (match, hour, minute, second, frac, meridian) {
            return this.time(satechjobs_processMeridian(+hour, meridian), +minute, +second, +frac.substr(0, 3));
        }
    },
    timeLong12: {
        regex: RegExp('^' + reHour12 + '[:.]' + reMinute + '[:.]' + reSecondlz + reSpaceOpt + reMeridian, 'i'),
        name: 'timelong12',
        callback: function (match, hour, minute, second, meridian) {
            return this.time(satechjobs_processMeridian(+hour, meridian), +minute, +second, 0);
        }
    },
    timeShort12: {
        regex: RegExp('^' + reHour12 + '[:.]' + reMinutelz + reSpaceOpt + reMeridian, 'i'),
        name: 'timeshort12',
        callback: function (match, hour, minute, meridian) {
            return this.time(satechjobs_processMeridian(+hour, meridian), +minute, 0, 0);
        }
    },
    timeTiny12: {
        regex: RegExp('^' + reHour12 + reSpaceOpt + reMeridian, 'i'),
        name: 'timetiny12',
        callback: function (match, hour, meridian) {
            return this.time(satechjobs_processMeridian(+hour, meridian), 0, 0, 0);
        }
    },
    soap: {
        regex: RegExp('^' + reYear4 + '-' + reMonthlz + '-' + reDaylz + 'T' + reHour24lz + ':' + reMinutelz + ':' + reSecondlz + reFrac + reTzCorrection + '?', 'i'),
        name: 'soap',
        callback: function (match, year, month, day, hour, minute, second, frac, tzCorrection) {
            return this.ymd(+year, month - 1, +day) &&
                this.time(+hour, +minute, +second, +frac.substr(0, 3)) &&
                this.zone(processTzCorrection(tzCorrection));
        }
    },
    wddx: {
        regex: RegExp('^' + reYear4 + '-' + reMonth + '-' + reDay + 'T' + reHour24 + ':' + reMinute + ':' + reSecond),
        name: 'wddx',
        callback: function (match, year, month, day, hour, minute, second) {
            return this.ymd(+year, month - 1, +day) && this.time(+hour, +minute, +second, 0);
        }
    },
    exif: {
        regex: RegExp('^' + reYear4 + ':' + reMonthlz + ':' + reDaylz + ' ' + reHour24lz + ':' + reMinutelz + ':' + reSecondlz, 'i'),
        name: 'exif',
        callback: function (match, year, month, day, hour, minute, second) {
            return this.ymd(+year, month - 1, +day) && this.time(+hour, +minute, +second, 0);
        }
    },
    xmlRpc: {
        regex: RegExp('^' + reYear4 + reMonthlz + reDaylz + 'T' + reHour24 + ':' + reMinutelz + ':' + reSecondlz),
        name: 'xmlrpc',
        callback: function (match, year, month, day, hour, minute, second) {
            return this.ymd(+year, month - 1, +day) && this.time(+hour, +minute, +second, 0);
        }
    },
    xmlRpcNoColon: {
        regex: RegExp('^' + reYear4 + reMonthlz + reDaylz + '[Tt]' + reHour24 + reMinutelz + reSecondlz),
        name: 'xmlrpcnocolon',
        callback: function (match, year, month, day, hour, minute, second) {
            return this.ymd(+year, month - 1, +day) && this.time(+hour, +minute, +second, 0);
        }
    },
    clf: {
        regex: RegExp('^' + reDay + '/(' + reMonthAbbr + ')/' + reYear4 + ':' + reHour24lz + ':' + reMinutelz + ':' + reSecondlz + reSpace + reTzCorrection, 'i'),
        name: 'clf',
        callback: function (match, day, month, year, hour, minute, second, tzCorrection) {
            return this.ymd(+year, lookupMonth(month), +day) &&
                this.time(+hour, +minute, +second, 0) &&
                this.zone(processTzCorrection(tzCorrection));
        }
    },
    iso8601long: {
        regex: RegExp('^t?' + reHour24 + '[:.]' + reMinute + '[:.]' + reSecond + reFrac, 'i'),
        name: 'iso8601long',
        callback: function (match, hour, minute, second, frac) {
            return this.time(+hour, +minute, +second, +frac.substr(0, 3));
        }
    },
    dateTextual: {
        regex: RegExp('^' + reMonthText + '[ .\\t-]*' + reDay + '[,.stndrh\\t ]+' + reYear, 'i'),
        name: 'datetextual',
        callback: function (match, month, day, year) {
            return this.ymd(processYear(year), lookupMonth(month), +day);
        }
    },
    pointedDate4: {
        regex: RegExp('^' + reDay + '[.\\t-]' + reMonth + '[.-]' + reYear4),
        name: 'pointeddate4',
        callback: function (match, day, month, year) {
            return this.ymd(+year, month - 1, +day);
        }
    },
    pointedDate2: {
        regex: RegExp('^' + reDay + '[.\\t]' + reMonth + '\\.' + reYear2),
        name: 'pointeddate2',
        callback: function (match, day, month, year) {
            return this.ymd(processYear(year), month - 1, +day);
        }
    },
    timeLong24: {
        regex: RegExp('^t?' + reHour24 + '[:.]' + reMinute + '[:.]' + reSecond),
        name: 'timelong24',
        callback: function (match, hour, minute, second) {
            return this.time(+hour, +minute, +second, 0);
        }
    },
    dateNoColon: {
        regex: RegExp('^' + reYear4 + reMonthlz + reDaylz),
        name: 'datenocolon',
        callback: function (match, year, month, day) {
            return this.ymd(+year, month - 1, +day);
        }
    },
    pgydotd: {
        regex: RegExp('^' + reYear4 + '\\.?' + reDayOfYear),
        name: 'pgydotd',
        callback: function (match, year, day) {
            return this.ymd(+year, 0, +day);
        }
    },
    timeShort24: {
        regex: RegExp('^t?' + reHour24 + '[:.]' + reMinute, 'i'),
        name: 'timeshort24',
        callback: function (match, hour, minute) {
            return this.time(+hour, +minute, 0, 0);
        }
    },
    iso8601noColon: {
        regex: RegExp('^t?' + reHour24lz + reMinutelz + reSecondlz, 'i'),
        name: 'iso8601nocolon',
        callback: function (match, hour, minute, second) {
            return this.time(+hour, +minute, +second, 0);
        }
    },
    iso8601dateSlash: {
        regex: RegExp('^' + reYear4 + '/' + reMonthlz + '/' + reDaylz + '/'),
        name: 'iso8601dateslash',
        callback: function (match, year, month, day) {
            return this.ymd(+year, month - 1, +day);
        }
    },
    dateSlash: {
        regex: RegExp('^' + reYear4 + '/' + reMonth + '/' + reDay),
        name: 'dateslash',
        callback: function (match, year, month, day) {
            return this.ymd(+year, month - 1, +day);
        }
    },
    american: {
        regex: RegExp('^' + reMonth + '/' + reDay + '/' + reYear),
        name: 'american',
        callback: function (match, month, day, year) {
            return this.ymd(processYear(year), month - 1, +day);
        }
    },
    americanShort: {
        regex: RegExp('^' + reMonth + '/' + reDay),
        name: 'americanshort',
        callback: function (match, month, day) {
            return this.ymd(this.y, month - 1, +day);
        }
    },
    gnuDateShortOrIso8601date2: {
        regex: RegExp('^' + reYear + '-' + reMonth + '-' + reDay),
        name: 'gnudateshort | iso8601date2',
        callback: function (match, year, month, day) {
            return this.ymd(processYear(year), month - 1, +day);
        }
    },
    iso8601date4: {
        regex: RegExp('^' + reYear4withSign + '-' + reMonthlz + '-' + reDaylz),
        name: 'iso8601date4',
        callback: function (match, year, month, day) {
            return this.ymd(+year, month - 1, +day);
        }
    },
    gnuNoColon: {
        regex: RegExp('^t' + reHour24lz + reMinutelz, 'i'),
        name: 'gnunocolon',
        callback: function (match, hour, minute) {
            return this.time(+hour, +minute, 0, this.f);
        }
    },
    gnuDateShorter: {
        regex: RegExp('^' + reYear4 + '-' + reMonth),
        name: 'gnudateshorter',
        callback: function (match, year, month) {
            return this.ymd(+year, month - 1, 1);
        }
    },
    pgTextReverse: {
        regex: RegExp('^' + '(\\d{3,4}|[4-9]\\d|3[2-9])-(' + reMonthAbbr + ')-' + reDaylz, 'i'),
        name: 'pgtextreverse',
        callback: function (match, year, month, day) {
            return this.ymd(processYear(year), lookupMonth(month), +day);
        }
    },
    dateFull: {
        regex: RegExp('^' + reDay + '[ \\t.-]*' + reMonthText + '[ \\t.-]*' + reYear, 'i'),
        name: 'datefull',
        callback: function (match, day, month, year) {
            return this.ymd(processYear(year), lookupMonth(month), +day);
        }
    },
    dateNoDay: {
        regex: RegExp('^' + reMonthText + '[ .\\t-]*' + reYear4, 'i'),
        name: 'datenoday',
        callback: function (match, month, year) {
            return this.ymd(+year, lookupMonth(month), 1);
        }
    },
    dateNoDayRev: {
        regex: RegExp('^' + reYear4 + '[ .\\t-]*' + reMonthText, 'i'),
        name: 'datenodayrev',
        callback: function (match, year, month) {
            return this.ymd(+year, lookupMonth(month), 1);
        }
    },
    pgTextShort: {
        regex: RegExp('^(' + reMonthAbbr + ')-' + reDaylz + '-' + reYear, 'i'),
        name: 'pgtextshort',
        callback: function (match, month, day, year) {
            return this.ymd(processYear(year), lookupMonth(month), +day);
        }
    },
    dateNoYear: {
        regex: RegExp('^' + reMonthText + '[ .\\t-]*' + reDay + '[,.stndrh\\t ]*', 'i'),
        name: 'datenoyear',
        callback: function (match, month, day) {
            return this.ymd(this.y, lookupMonth(month), +day);
        }
    },
    dateNoYearRev: {
        regex: RegExp('^' + reDay + '[ .\\t-]*' + reMonthText, 'i'),
        name: 'datenoyearrev',
        callback: function (match, day, month) {
            return this.ymd(this.y, lookupMonth(month), +day);
        }
    },
    isoWeekDay: {
        regex: RegExp('^' + reYear4 + '-?W' + reWeekOfYear + '(?:-?([0-7]))?'),
        name: 'isoweekday | isoweek',
        callback: function (match, year, week, day) {
            day = day ? +day : 1;
            if (!this.ymd(+year, 0, 1)) {
                return false;
            }
            var dayOfWeek = new Date(this.y, this.m, this.d).getDay();
            dayOfWeek = 0 - (dayOfWeek > 4 ? dayOfWeek - 7 : dayOfWeek);
            this.rd += dayOfWeek + ((week - 1) * 7) + day;
        }
    },
    relativeText: {
        regex: RegExp('^(' + reReltextnumber + '|' + reReltexttext + ')' + reSpace + '(' + reReltextunit + ')', 'i'),
        name: 'relativetext',
        callback: function (match, relValue, relUnit) {
            var _a = lookupRelative(relValue), amount = _a.amount, behavior = _a.behavior;
            switch (relUnit.toLowerCase()) {
                case 'sec':
                case 'secs':
                case 'second':
                case 'seconds':
                    this.rs += amount;
                    break;
                case 'min':
                case 'mins':
                case 'minute':
                case 'minutes':
                    this.ri += amount;
                    break;
                case 'hour':
                case 'hours':
                    this.rh += amount;
                    break;
                case 'day':
                case 'days':
                    this.rd += amount;
                    break;
                case 'fortnight':
                case 'fortnights':
                case 'forthnight':
                case 'forthnights':
                    this.rd += amount * 14;
                    break;
                case 'week':
                case 'weeks':
                    this.rd += amount * 7;
                    break;
                case 'month':
                case 'months':
                    this.rm += amount;
                    break;
                case 'year':
                case 'years':
                    this.ry += amount;
                    break;
                case 'mon':
                case 'monday':
                case 'tue':
                case 'tuesday':
                case 'wed':
                case 'wednesday':
                case 'thu':
                case 'thursday':
                case 'fri':
                case 'friday':
                case 'sat':
                case 'saturday':
                case 'sun':
                case 'sunday':
                    this.resetTime();
                    this.weekday = lookupWeekday(relUnit, 7);
                    this.weekdayBehavior = 1;
                    this.rd += (amount > 0 ? amount - 1 : amount) * 7;
                    break;
                case 'weekday':
                case 'weekdays':
                    break;
            }
        }
    },
    relative: {
        regex: RegExp('^([+-]*)[ \\t]*(\\d+)' + reSpaceOpt + '(' + reReltextunit + '|week)', 'i'),
        name: 'relative',
        callback: function (match, signs, relValue, relUnit) {
            var minuses = signs.replace(/[^-]/g, '').length;
            var amount = +relValue * Math.pow(-1, minuses);
            switch (relUnit.toLowerCase()) {
                case 'sec':
                case 'secs':
                case 'second':
                case 'seconds':
                    this.rs += amount;
                    break;
                case 'min':
                case 'mins':
                case 'minute':
                case 'minutes':
                    this.ri += amount;
                    break;
                case 'hour':
                case 'hours':
                    this.rh += amount;
                    break;
                case 'day':
                case 'days':
                    this.rd += amount;
                    break;
                case 'fortnight':
                case 'fortnights':
                case 'forthnight':
                case 'forthnights':
                    this.rd += amount * 14;
                    break;
                case 'week':
                case 'weeks':
                    this.rd += amount * 7;
                    break;
                case 'month':
                case 'months':
                    this.rm += amount;
                    break;
                case 'year':
                case 'years':
                    this.ry += amount;
                    break;
                case 'mon':
                case 'monday':
                case 'tue':
                case 'tuesday':
                case 'wed':
                case 'wednesday':
                case 'thu':
                case 'thursday':
                case 'fri':
                case 'friday':
                case 'sat':
                case 'saturday':
                case 'sun':
                case 'sunday':
                    this.resetTime();
                    this.weekday = lookupWeekday(relUnit, 7);
                    this.weekdayBehavior = 1;
                    this.rd += (amount > 0 ? amount - 1 : amount) * 7;
                    break;
                case 'weekday':
                case 'weekdays':
                    break;
            }
        }
    },
    dayText: {
        regex: RegExp('^(' + reDaytext + ')', 'i'),
        name: 'daytext',
        callback: function (match, dayText) {
            this.resetTime();
            this.weekday = lookupWeekday(dayText, 0);
            if (this.weekdayBehavior !== 2) {
                this.weekdayBehavior = 1;
            }
        }
    },
    relativeTextWeek: {
        regex: RegExp('^(' + reReltexttext + ')' + reSpace + 'week', 'i'),
        name: 'relativetextweek',
        callback: function (match, relText) {
            this.weekdayBehavior = 2;
            switch (relText.toLowerCase()) {
                case 'this':
                    this.rd += 0;
                    break;
                case 'next':
                    this.rd += 7;
                    break;
                case 'last':
                case 'previous':
                    this.rd -= 7;
                    break;
            }
            if (isNaN(this.weekday)) {
                this.weekday = 1;
            }
        }
    },
    monthFullOrMonthAbbr: {
        regex: RegExp('^(' + reMonthFull + '|' + reMonthAbbr + ')', 'i'),
        name: 'monthfull | monthabbr',
        callback: function (match, month) {
            return this.ymd(this.y, lookupMonth(month), this.d);
        }
    },
    tzCorrection: {
        regex: RegExp('^' + reTzCorrection, 'i'),
        name: 'tzcorrection',
        callback: function (tzCorrection) {
            return this.zone(processTzCorrection(tzCorrection));
        }
    },
    ago: {
        regex: /^ago/i,
        name: 'ago',
        callback: function () {
            this.ry = -this.ry;
            this.rm = -this.rm;
            this.rd = -this.rd;
            this.rh = -this.rh;
            this.ri = -this.ri;
            this.rs = -this.rs;
            this.rf = -this.rf;
        }
    },
    gnuNoColon2: {
        regex: RegExp('^' + reHour24lz + reMinutelz, 'i'),
        name: 'gnunocolon',
        callback: function (match, hour, minute) {
            return this.time(+hour, +minute, 0, this.f);
        }
    },
    year4: {
        regex: RegExp('^' + reYear4),
        name: 'year4',
        callback: function (match, year) {
            this.y = +year;
            return true;
        }
    },
    whitespace: {
        regex: /^[ .,\t]+/,
        name: 'whitespace'
    },
    any: {
        regex: /^[\s\S]+/,
        name: 'any',
        callback: function () {
            return false;
        }
    }
};
var resultProto = {
    y: NaN,
    m: NaN,
    d: NaN,
    h: NaN,
    i: NaN,
    s: NaN,
    f: NaN,
    ry: 0,
    rm: 0,
    rd: 0,
    rh: 0,
    ri: 0,
    rs: 0,
    rf: 0,
    weekday: NaN,
    weekdayBehavior: 0,
    firstOrLastDayOfMonth: 0,
    z: NaN,
    dates: 0,
    times: 0,
    zones: 0,
    ymd: function (y, m, d) {
        if (this.dates > 0) {
            return false;
        }
        this.dates++;
        this.y = y;
        this.m = m;
        this.d = d;
        return true;
    },
    time: function (h, i, s, f) {
        if (this.times > 0) {
            return false;
        }
        this.times++;
        this.h = h;
        this.i = i;
        this.s = s;
        this.f = f;
        return true;
    },
    resetTime: function () {
        this.h = 0;
        this.i = 0;
        this.s = 0;
        this.f = 0;
        this.times = 0;
        return true;
    },
    zone: function (minutes) {
        if (this.zones <= 1) {
            this.zones++;
            this.z = minutes;
            return true;
        }
        return false;
    },
    toDate: function (relativeTo) {
        if (this.dates && !this.times) {
            this.h = this.i = this.s = this.f = 0;
        }
        if (isNaN(this.y)) {
            this.y = relativeTo.getFullYear();
        }
        if (isNaN(this.m)) {
            this.m = relativeTo.getMonth();
        }
        if (isNaN(this.d)) {
            this.d = relativeTo.getDate();
        }
        if (isNaN(this.h)) {
            this.h = relativeTo.getHours();
        }
        if (isNaN(this.i)) {
            this.i = relativeTo.getMinutes();
        }
        if (isNaN(this.s)) {
            this.s = relativeTo.getSeconds();
        }
        if (isNaN(this.f)) {
            this.f = relativeTo.getMilliseconds();
        }
        switch (this.firstOrLastDayOfMonth) {
            case 1:
                this.d = 1;
                break;
            case -1:
                this.d = 0;
                this.m += 1;
                break;
        }
        if (!isNaN(this.weekday)) {
            var date = new Date(relativeTo.getTime());
            date.setFullYear(this.y, this.m, this.d);
            date.setHours(this.h, this.i, this.s, this.f);
            var dow = date.getDay();
            if (this.weekdayBehavior === 2) {
                if (dow === 0 && this.weekday !== 0) {
                    this.weekday = -6;
                }
                if (this.weekday === 0 && dow !== 0) {
                    this.weekday = 7;
                }
                this.d -= dow;
                this.d += this.weekday;
            }
            else {
                var diff = this.weekday - dow;
                if ((this.rd < 0 && diff < 0) || (this.rd >= 0 && diff <= -this.weekdayBehavior)) {
                    diff += 7;
                }
                if (this.weekday >= 0) {
                    this.d += diff;
                }
                else {
                    this.d -= (7 - (Math.abs(this.weekday) - dow));
                }
                this.weekday = NaN;
            }
        }
        this.y += this.ry;
        this.m += this.rm;
        this.d += this.rd;
        this.h += this.rh;
        this.i += this.ri;
        this.s += this.rs;
        this.f += this.rf;
        this.ry = this.rm = this.rd = 0;
        this.rh = this.ri = this.rs = this.rf = 0;
        var result = new Date(relativeTo.getTime());
        result.setFullYear(this.y, this.m, this.d);
        result.setHours(this.h, this.i, this.s, this.f);
        switch (this.firstOrLastDayOfMonth) {
            case 1:
                result.setDate(1);
                break;
            case -1:
                result.setMonth(result.getMonth() + 1, 0);
                break;
        }
        if (!isNaN(this.z) && result.getTimezoneOffset() !== this.z) {
            result.setUTCFullYear(result.getFullYear(), result.getMonth(), result.getDate());
            result.setUTCHours(result.getHours(), result.getMinutes() + this.z, result.getSeconds(), result.getMilliseconds());
        }
        return result;
    }
};
function satechjobs_strtotime(str, now) {
    if (now == null) {
        now = Math.floor(Date.now() / 1000);
    }
    var rules = [
        formats.yesterday,
        formats.now,
        formats.noon,
        formats.midnightOrToday,
        formats.tomorrow,
        formats.timestamp,
        formats.firstOrLastDay,
        formats.backOrFrontOf,
        formats.mssqltime,
        formats.timeLong12,
        formats.timeShort12,
        formats.timeTiny12,
        formats.soap,
        formats.wddx,
        formats.exif,
        formats.xmlRpc,
        formats.xmlRpcNoColon,
        formats.clf,
        formats.iso8601long,
        formats.dateTextual,
        formats.pointedDate4,
        formats.pointedDate2,
        formats.timeLong24,
        formats.dateNoColon,
        formats.pgydotd,
        formats.timeShort24,
        formats.iso8601noColon,
        formats.iso8601dateSlash,
        formats.dateSlash,
        formats.american,
        formats.americanShort,
        formats.gnuDateShortOrIso8601date2,
        formats.iso8601date4,
        formats.gnuNoColon,
        formats.gnuDateShorter,
        formats.pgTextReverse,
        formats.dateFull,
        formats.dateNoDay,
        formats.dateNoDayRev,
        formats.pgTextShort,
        formats.dateNoYear,
        formats.dateNoYearRev,
        formats.isoWeekDay,
        formats.relativeText,
        formats.relative,
        formats.dayText,
        formats.relativeTextWeek,
        formats.monthFullOrMonthAbbr,
        formats.tzCorrection,
        formats.ago,
        formats.gnuNoColon2,
        formats.year4,
        formats.whitespace,
        formats.any
    ];
    var result = Object.create(resultProto);
    while (str.length) {
        for (var i = 0, l = rules.length; i < l; i++) {
            var format = rules[i];
            var match = str.match(format.regex);
            if (match) {
                if (format.callback && format.callback.apply(result, match) === false) {
                    return false;
                }
                str = str.substr(match[0].length);
                break;
            }
        }
    }
    return Math.floor(result.toDate(new Date(now * 1000)) / 1000);
}
function satechjobs_nl2br(str, isXhtml) {
    if (str === void 0) { str = null; }
    if (str === null) {
        return '';
    }
    var breakTag = (isXhtml || typeof isXhtml === 'undefined') ? '<br ' + '/>' : '<br>';
    return (str + '')
        .replace(/(\r\n|\n\r|\r|\n)/g, breakTag + '$1');
}
function satechjobs_date(format, timestamp) {
    var jsdate, f;
    var txtWords = [
        'Sun', 'Mon', 'Tues', 'Wednes', 'Thurs', 'Fri', 'Satur',
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    var formatChr = /\\?(.?)/gi;
    var formatChrCb = function (t, s) {
        return f[t] ? f[t]() : s;
    };
    var _pad = function (n, c) {
        n = String(n);
        while (n.length < c) {
            n = '0' + n;
        }
        return n;
    };
    f = {
        d: function () {
            return _pad(f.j(), 2);
        },
        D: function () {
            return f.l()
                .slice(0, 3);
        },
        j: function () {
            return jsdate.getDate();
        },
        l: function () {
            return txtWords[f.w()] + 'day';
        },
        N: function () {
            return f.w() || 7;
        },
        S: function () {
            var j = f.j();
            var i = j % 10;
            if (i <= 3 && parseInt(String((j % 100) / 10), 10) === 1) {
                i = 0;
            }
            return ['st', 'nd', 'rd'][i - 1] || 'th';
        },
        w: function () {
            return jsdate.getDay();
        },
        z: function () {
            var a = new Date(f.Y(), f.n() - 1, f.j());
            var b = new Date(f.Y(), 0, 1);
            return Math.round((a - b) / 864e5);
        },
        W: function () {
            var a = new Date(f.Y(), f.n() - 1, f.j() - f.N() + 3);
            var b = new Date(a.getFullYear(), 0, 4);
            return _pad(1 + Math.round((a - b) / 864e5 / 7), 2);
        },
        F: function () {
            return txtWords[6 + f.n()];
        },
        m: function () {
            return _pad(f.n(), 2);
        },
        M: function () {
            return f.F()
                .slice(0, 3);
        },
        n: function () {
            return jsdate.getMonth() + 1;
        },
        t: function () {
            return (new Date(f.Y(), f.n(), 0))
                .getDate();
        },
        L: function () {
            var j = f.Y();
            return j % 4 === 0 & j % 100 !== 0 | j % 400 === 0;
        },
        o: function () {
            var n = f.n();
            var W = f.W();
            var Y = f.Y();
            return Y + (n === 12 && W < 9 ? 1 : n === 1 && W > 9 ? -1 : 0);
        },
        Y: function () {
            return jsdate.getFullYear();
        },
        y: function () {
            return f.Y()
                .toString()
                .slice(-2);
        },
        a: function () {
            return jsdate.getHours() > 11 ? 'pm' : 'am';
        },
        A: function () {
            return f.a()
                .toUpperCase();
        },
        B: function () {
            var H = jsdate.getUTCHours() * 36e2;
            var i = jsdate.getUTCMinutes() * 60;
            var s = jsdate.getUTCSeconds();
            return _pad(Math.floor((H + i + s + 36e2) / 86.4) % 1e3, 3);
        },
        g: function () {
            return f.G() % 12 || 12;
        },
        G: function () {
            return jsdate.getHours();
        },
        h: function () {
            return _pad(f.g(), 2);
        },
        H: function () {
            return _pad(f.G(), 2);
        },
        i: function () {
            return _pad(jsdate.getMinutes(), 2);
        },
        s: function () {
            return _pad(jsdate.getSeconds(), 2);
        },
        u: function () {
            return _pad(jsdate.getMilliseconds() * 1000, 6);
        },
        e: function () {
            var msg = 'Not supported (see source code of date() for timezone on how to add support)';
            throw new Error(msg);
        },
        I: function () {
            var a = new Date(f.Y(), 0);
            var c = Date.UTC(f.Y(), 0);
            var b = new Date(f.Y(), 6);
            var d = Date.UTC(f.Y(), 6);
            return ((a - c) !== (b - d)) ? 1 : 0;
        },
        O: function () {
            var tzo = jsdate.getTimezoneOffset();
            var a = Math.abs(tzo);
            return (tzo > 0 ? '-' : '+') + _pad(Math.floor(a / 60) * 100 + a % 60, 4);
        },
        P: function () {
            var O = f.O();
            return (O.substr(0, 3) + ':' + O.substr(3, 2));
        },
        T: function () {
            return 'UTC';
        },
        Z: function () {
            return -jsdate.getTimezoneOffset() * 60;
        },
        c: function () {
            return 'Y-m-d\\TH:i:sP'.replace(formatChr, formatChrCb);
        },
        r: function () {
            return 'D, d M Y H:i:s O'.replace(formatChr, formatChrCb);
        },
        U: function () {
            return jsdate / 1000 | 0;
        }
    };
    var _date = function (format, timestamp) {
        jsdate = (timestamp === undefined ? new Date()
            : (timestamp instanceof Date) ? new Date(timestamp)
                : new Date(timestamp * 1000));
        return format.replace(formatChr, formatChrCb);
    };
    return _date(format, timestamp);
}
function SATechJobsError(errorMessage) {
    jQuery.alert({
        boxWidth: '350px',
        useBootstrap: false,
        type: 'red',
        content: errorMessage,
        typeAnimated: true,
        title: 'Error!',
        icon: 'fa fa-exclamation-triangle',
    });
}
function SATechJobsSuccess(Message) {
    jQuery.alert({
        boxWidth: '350px',
        useBootstrap: false,
        type: 'green',
        content: Message,
        typeAnimated: true,
        title: 'Success',
        icon: 'fa fa-check',
    });
}
function SATechJobsWarning(Message) {
    jQuery.alert({
        boxWidth: '350px',
        useBootstrap: false,
        type: 'orange',
        content: Message,
        typeAnimated: true,
        title: 'Warning',
        icon: 'fa fa-exclamation-circle',
    });
}
function satechjobs_strip_tags(str) {
    str = str.toString();
    return str.replace(/<\/?[^>]+>/gi, '');
}
function satechjobs_stripcslashes(str) {
    var target = '', i = 0, sl = 0, s = '', next = '', hex = '', oct = '', hex2DigMax = /[\dA-Fa-f]{1,2}/, rest = '', seq = '', oct3DigMaxs = /([0-7]{1,3})((\\[0-7]{1,3})*)/, oct3DigMax = /(\\([0-7]{1,3}))*/g, escOctGrp = [];
    for (i = 0, sl = str.length; i < sl; i++) {
        s = str.charAt(i);
        next = str.charAt(i + 1);
        if (s !== '\\' || !next) {
            target += s;
            continue;
        }
        switch (next) {
            case 'r':
                target += '\u000D';
                break;
            case 'a':
                target += '\u0007';
                break;
            case 'n':
                target += '\n';
                break;
            case 't':
                target += '\t';
                break;
            case 'v':
                target += '\v';
                break;
            case 'b':
                target += '\b';
                break;
            case 'f':
                target += '\f';
                break;
            case '\\':
                target += '\\';
                break;
            case 'x':
                rest = str.slice(i + 2);
                if (rest.search(hex2DigMax) !== -1) {
                    hex = (hex2DigMax).exec(rest);
                    i += hex.length;
                    target += String.fromCharCode(parseInt(hex, 16));
                    break;
                }
            default:
                rest = str.slice(i + 2);
                if (rest.search(oct3DigMaxs) !== -1) {
                    oct = (oct3DigMaxs).exec(rest);
                    i += oct[1].length;
                    rest = str.slice(i + 2);
                    seq = '';
                    if ((escOctGrp = oct3DigMax.exec(rest)) !== null) {
                        seq += '%' + parseInt(escOctGrp[2], 8).toString(16);
                    }
                    try {
                        target += decodeURIComponent(seq);
                    }
                    catch (e) {
                        target += '\uFFFD';
                    }
                    break;
                }
                target += next;
                break;
        }
        ++i;
    }
    return target;
}
function satechjobs_stripslashes(str) {
    return (str + '')
        .replace(/\\(.?)/g, function (s, n1) {
        switch (n1) {
            case '\\':
                return '\\';
            case '0':
                return '\u0000';
            case '':
                return '';
            default:
                return n1;
        }
    });
}
function tabClick(e) {
    var target = jQuery(this).attr('data-target');
    jQuery('div.tabs ul li').removeClass('is-active');
    jQuery(this).addClass('is-active');
    jQuery('div.tab-content>div').hide();
    jQuery('div.tab-content div#' + target).show();
}
function get_file_extension(filename) {
    var Extension = filename.split('.').pop();
    return Extension.toLowerCase();
}
function is_image_file(filename) {
    var Ext = get_file_extension(filename);
    switch (Ext) {
        case 'jpg':
        case 'jpeg':
        case 'gif':
        case 'png':
        case 'bmp':
        case 'tiff':
        case 'jiff':
            return true;
        default:
            return false;
    }
}
jQuery(function () {
    jQuery(document).on('click', 'div.tabs ul li', tabClick);
});
function satechjobs_count(mixedVar, mode) {
    var key;
    var cnt = 0;
    if (mixedVar === null || typeof mixedVar === 'undefined') {
        return 0;
    }
    else if (mixedVar.constructor !== Array && mixedVar.constructor !== Object) {
        return 1;
    }
    if (mode === 'COUNT_RECURSIVE') {
        mode = 1;
    }
    if (mode !== 1) {
        mode = 0;
    }
    for (key in mixedVar) {
        if (mixedVar.hasOwnProperty(key)) {
            cnt++;
            if (mode === 1 && mixedVar[key] &&
                (mixedVar[key].constructor === Array ||
                    mixedVar[key].constructor === Object)) {
                cnt += satechjobs_count(mixedVar[key], 1);
            }
        }
    }
    return cnt;
}
function satechjobs_str_replace(search, replace, subject, countObj) {
    var i = 0;
    var j = 0;
    var temp = '';
    var repl = '';
    var sl = 0;
    var fl = 0;
    var f = [].concat(search);
    var r = [].concat(replace);
    var s = subject;
    var ra = Object.prototype.toString.call(r) === '[object Array]';
    var sa = Object.prototype.toString.call(s) === '[object Array]';
    s = [].concat(s);
    var $global = (typeof window !== 'undefined' ? window : global);
    $global.$locutus = $global.$locutus || {};
    var $locutus = $global.$locutus;
    $locutus.php = $locutus.php || {};
    if (typeof (search) === 'object' && typeof (replace) === 'string') {
        temp = replace;
        replace = [];
        for (i = 0; i < search.length; i += 1) {
            replace[i] = temp;
        }
        temp = '';
        r = [].concat(replace);
        ra = Object.prototype.toString.call(r) === '[object Array]';
    }
    if (typeof countObj !== 'undefined') {
        countObj.value = 0;
    }
    for (i = 0, sl = s.length; i < sl; i++) {
        if (s[i] === '') {
            continue;
        }
        for (j = 0, fl = f.length; j < fl; j++) {
            temp = s[i] + '';
            repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
            s[i] = (temp).split(f[j]).join(repl);
            if (typeof countObj !== 'undefined') {
                countObj.value += ((temp.split(f[j])).length - 1);
            }
        }
    }
    return sa ? s : s[0];
}
//# sourceMappingURL=functions.js.map