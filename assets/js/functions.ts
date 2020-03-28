"use strict";

const reSpace = '[ \\t]+';
const reSpaceOpt = '[ \\t]*';
const reMeridian = '(?:([ap])\\.?m\\.?([\\t ]|$))';
const reHour24 = '(2[0-4]|[01]?[0-9])';
const reHour24lz = '([01][0-9]|2[0-4])';
const reHour12 = '(0?[1-9]|1[0-2])';
const reMinute = '([0-5]?[0-9])';
const reMinutelz = '([0-5][0-9])';
const reSecond = '(60|[0-5]?[0-9])';
const reSecondlz = '(60|[0-5][0-9])';
const reFrac = '(?:\\.([0-9]+))';

const reDayfull = 'sunday|monday|tuesday|wednesday|thursday|friday|saturday';
const reDayabbr = 'sun|mon|tue|wed|thu|fri|sat';
const reDaytext = reDayfull + '|' + reDayabbr + '|weekdays?';

const reReltextnumber = 'first|second|third|fourth|fifth|sixth|seventh|eighth?|ninth|tenth|eleventh|twelfth';
const reReltexttext = 'next|last|previous|this';
const reReltextunit = '(?:second|sec|minute|min|hour|day|fortnight|forthnight|month|year)s?|weeks|' + reDaytext;

const reYear = '([0-9]{1,4})';
const reYear2 = '([0-9]{2})';
const reYear4 = '([0-9]{4})';
const reYear4withSign = '([+-]?[0-9]{4})';
const reMonth = '(1[0-2]|0?[0-9])';
const reMonthlz = '(0[0-9]|1[0-2])';
const reDay = '(?:(3[01]|[0-2]?[0-9])(?:st|nd|rd|th)?)';
const reDaylz = '(0[0-9]|[1-2][0-9]|3[01])';

const reMonthFull = 'january|february|march|april|may|june|july|august|september|october|november|december';
const reMonthAbbr = 'jan|feb|mar|apr|may|jun|jul|aug|sept?|oct|nov|dec';
const reMonthroman = 'i[vx]|vi{0,3}|xi{0,2}|i{1,3}';
const reMonthText = '(' + reMonthFull + '|' + reMonthAbbr + '|' + reMonthroman + ')';

const reTzCorrection = '((?:GMT)?([+-])' + reHour24 + ':?' + reMinute + '?)';
const reDayOfYear = '(00[1-9]|0[1-9][0-9]|[12][0-9][0-9]|3[0-5][0-9]|36[0-6])';
const reWeekOfYear = '(0[1-9]|[1-4][0-9]|5[0-3])';

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
    let year = +yearStr;

    if (yearStr.length < 4 && year < 100) {
        year += year < 70 ? 2000 : 1900
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
    }[monthStr.toLowerCase()]
}

function lookupWeekday(dayStr, desiredSundayNumber = 0) {
    const dayNumbers = {
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
    const relativeNumbers = {
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

    const relativeBehavior = {
        this: 1
    };

    const relTextLower = relText.toLowerCase();

    return {
        amount: relativeNumbers[relTextLower],
        behavior: relativeBehavior[relTextLower] || 0
    }
}

function processTzCorrection(tzOffset, oldValue = null) {
    const reTzCorrectionLoose = /(?:GMT)?([+-])(\d+)(:?)(\d{0,2})/i;
    tzOffset = tzOffset && tzOffset.match(reTzCorrectionLoose);

    if (!tzOffset) {
        return oldValue
    }

    let sign = tzOffset[1] === '-' ? 1 : -1;
    let hours = +tzOffset[2];
    let minutes = +tzOffset[4];

    if (!tzOffset[4] && !tzOffset[3]) {
        minutes = Math.floor(hours % 100);
        hours = Math.floor(hours / 100);
    }

    return sign * (hours * 60 + minutes)
}

const formats = {
    yesterday: {
        regex: /^yesterday/i,
        name: 'yesterday',
        callback() {
            this.rd -= 1;
            return this.resetTime();
        }
    },

    now: {
        regex: /^now/i,
        name: 'now'
        // do nothing
    },

    noon: {
        regex: /^noon/i,
        name: 'noon',
        callback() {
            return this.resetTime() && this.time(12, 0, 0, 0)
        }
    },

    midnightOrToday: {
        regex: /^(midnight|today)/i,
        name: 'midnight | today',
        callback() {
            return this.resetTime()
        }
    },

    tomorrow: {
        regex: /^tomorrow/i,
        name: 'tomorrow',
        callback() {
            this.rd += 1
            return this.resetTime()
        }
    },

    timestamp: {
        regex: /^@(-?\d+)/i,
        name: 'timestamp',
        callback(match, timestamp) {
            this.rs += +timestamp
            this.y = 1970
            this.m = 0
            this.d = 1
            this.dates = 0

            return this.resetTime() && this.zone(0)
        }
    },

    firstOrLastDay: {
        regex: /^(first|last) day of/i,
        name: 'firstdayof | lastdayof',
        callback(match, day) {
            if (day.toLowerCase() === 'first') {
                this.firstOrLastDayOfMonth = 1
            } else {
                this.firstOrLastDayOfMonth = -1
            }
        }
    },

    backOrFrontOf: {
        regex: RegExp('^(back|front) of ' + reHour24 + reSpaceOpt + reMeridian + '?', 'i'),
        name: 'backof | frontof',
        callback(match, side, hours, meridian) {
            let back = side.toLowerCase() === 'back'
            let hour = +hours
            let minute = 15

            if (!back) {
                hour -= 1
                minute = 45
            }

            hour = satechjobs_processMeridian(hour, meridian)

            return this.resetTime() && this.time(hour, minute, 0, 0)
        }
    },

    weekdayOf: {
        regex: RegExp('^(' + reReltextnumber + '|' + reReltexttext + ')' + reSpace + '(' + reDayfull + '|' + reDayabbr + ')' + reSpace + 'of', 'i'),
        name: 'weekdayof'
    },

    mssqltime: {
        regex: RegExp('^' + reHour12 + ':' + reMinutelz + ':' + reSecondlz + '[:.]([0-9]+)' + reMeridian, 'i'),
        name: 'mssqltime',
        callback(match, hour, minute, second, frac, meridian) {
            return this.time(satechjobs_processMeridian(+hour, meridian), +minute, +second, +frac.substr(0, 3))
        }
    },

    timeLong12: {
        regex: RegExp('^' + reHour12 + '[:.]' + reMinute + '[:.]' + reSecondlz + reSpaceOpt + reMeridian, 'i'),
        name: 'timelong12',
        callback(match, hour, minute, second, meridian) {
            return this.time(satechjobs_processMeridian(+hour, meridian), +minute, +second, 0)
        }
    },

    timeShort12: {
        regex: RegExp('^' + reHour12 + '[:.]' + reMinutelz + reSpaceOpt + reMeridian, 'i'),
        name: 'timeshort12',
        callback(match, hour, minute, meridian) {
            return this.time(satechjobs_processMeridian(+hour, meridian), +minute, 0, 0)
        }
    },

    timeTiny12: {
        regex: RegExp('^' + reHour12 + reSpaceOpt + reMeridian, 'i'),
        name: 'timetiny12',
        callback(match, hour, meridian) {
            return this.time(satechjobs_processMeridian(+hour, meridian), 0, 0, 0)
        }
    },

    soap: {
        regex: RegExp('^' + reYear4 + '-' + reMonthlz + '-' + reDaylz + 'T' + reHour24lz + ':' + reMinutelz + ':' + reSecondlz + reFrac + reTzCorrection + '?', 'i'),
        name: 'soap',
        callback(match, year, month, day, hour, minute, second, frac, tzCorrection) {
            return this.ymd(+year, month - 1, +day) &&
                this.time(+hour, +minute, +second, +frac.substr(0, 3)) &&
                this.zone(processTzCorrection(tzCorrection))
        }
    },

    wddx: {
        regex: RegExp('^' + reYear4 + '-' + reMonth + '-' + reDay + 'T' + reHour24 + ':' + reMinute + ':' + reSecond),
        name: 'wddx',
        callback(match, year, month, day, hour, minute, second) {
            return this.ymd(+year, month - 1, +day) && this.time(+hour, +minute, +second, 0)
        }
    },

    exif: {
        regex: RegExp('^' + reYear4 + ':' + reMonthlz + ':' + reDaylz + ' ' + reHour24lz + ':' + reMinutelz + ':' + reSecondlz, 'i'),
        name: 'exif',
        callback(match, year, month, day, hour, minute, second) {
            return this.ymd(+year, month - 1, +day) && this.time(+hour, +minute, +second, 0)
        }
    },

    xmlRpc: {
        regex: RegExp('^' + reYear4 + reMonthlz + reDaylz + 'T' + reHour24 + ':' + reMinutelz + ':' + reSecondlz),
        name: 'xmlrpc',
        callback(match, year, month, day, hour, minute, second) {
            return this.ymd(+year, month - 1, +day) && this.time(+hour, +minute, +second, 0)
        }
    },

    xmlRpcNoColon: {
        regex: RegExp('^' + reYear4 + reMonthlz + reDaylz + '[Tt]' + reHour24 + reMinutelz + reSecondlz),
        name: 'xmlrpcnocolon',
        callback(match, year, month, day, hour, minute, second) {
            return this.ymd(+year, month - 1, +day) && this.time(+hour, +minute, +second, 0)
        }
    },

    clf: {
        regex: RegExp('^' + reDay + '/(' + reMonthAbbr + ')/' + reYear4 + ':' + reHour24lz + ':' + reMinutelz + ':' + reSecondlz + reSpace + reTzCorrection, 'i'),
        name: 'clf',
        callback(match, day, month, year, hour, minute, second, tzCorrection) {
            return this.ymd(+year, lookupMonth(month), +day) &&
                this.time(+hour, +minute, +second, 0) &&
                this.zone(processTzCorrection(tzCorrection))
        }
    },

    iso8601long: {
        regex: RegExp('^t?' + reHour24 + '[:.]' + reMinute + '[:.]' + reSecond + reFrac, 'i'),
        name: 'iso8601long',
        callback(match, hour, minute, second, frac) {
            return this.time(+hour, +minute, +second, +frac.substr(0, 3))
        }
    },

    dateTextual: {
        regex: RegExp('^' + reMonthText + '[ .\\t-]*' + reDay + '[,.stndrh\\t ]+' + reYear, 'i'),
        name: 'datetextual',
        callback(match, month, day, year) {
            return this.ymd(processYear(year), lookupMonth(month), +day)
        }
    },

    pointedDate4: {
        regex: RegExp('^' + reDay + '[.\\t-]' + reMonth + '[.-]' + reYear4),
        name: 'pointeddate4',
        callback(match, day, month, year) {
            return this.ymd(+year, month - 1, +day)
        }
    },

    pointedDate2: {
        regex: RegExp('^' + reDay + '[.\\t]' + reMonth + '\\.' + reYear2),
        name: 'pointeddate2',
        callback(match, day, month, year) {
            return this.ymd(processYear(year), month - 1, +day)
        }
    },

    timeLong24: {
        regex: RegExp('^t?' + reHour24 + '[:.]' + reMinute + '[:.]' + reSecond),
        name: 'timelong24',
        callback(match, hour, minute, second) {
            return this.time(+hour, +minute, +second, 0)
        }
    },

    dateNoColon: {
        regex: RegExp('^' + reYear4 + reMonthlz + reDaylz),
        name: 'datenocolon',
        callback(match, year, month, day) {
            return this.ymd(+year, month - 1, +day)
        }
    },

    pgydotd: {
        regex: RegExp('^' + reYear4 + '\\.?' + reDayOfYear),
        name: 'pgydotd',
        callback(match, year, day) {
            return this.ymd(+year, 0, +day)
        }
    },

    timeShort24: {
        regex: RegExp('^t?' + reHour24 + '[:.]' + reMinute, 'i'),
        name: 'timeshort24',
        callback(match, hour, minute) {
            return this.time(+hour, +minute, 0, 0)
        }
    },

    iso8601noColon: {
        regex: RegExp('^t?' + reHour24lz + reMinutelz + reSecondlz, 'i'),
        name: 'iso8601nocolon',
        callback(match, hour, minute, second) {
            return this.time(+hour, +minute, +second, 0)
        }
    },

    iso8601dateSlash: {
        // eventhough the trailing slash is optional in PHP
        // here it's mandatory and inputs without the slash
        // are handled by dateslash
        regex: RegExp('^' + reYear4 + '/' + reMonthlz + '/' + reDaylz + '/'),
        name: 'iso8601dateslash',
        callback(match, year, month, day) {
            return this.ymd(+year, month - 1, +day)
        }
    },

    dateSlash: {
        regex: RegExp('^' + reYear4 + '/' + reMonth + '/' + reDay),
        name: 'dateslash',
        callback(match, year, month, day) {
            return this.ymd(+year, month - 1, +day)
        }
    },

    american: {
        regex: RegExp('^' + reMonth + '/' + reDay + '/' + reYear),
        name: 'american',
        callback(match, month, day, year) {
            return this.ymd(processYear(year), month - 1, +day)
        }
    },

    americanShort: {
        regex: RegExp('^' + reMonth + '/' + reDay),
        name: 'americanshort',
        callback(match, month, day) {
            return this.ymd(this.y, month - 1, +day)
        }
    },

    gnuDateShortOrIso8601date2: {
        // iso8601date2 is complete subset of gnudateshort
        regex: RegExp('^' + reYear + '-' + reMonth + '-' + reDay),
        name: 'gnudateshort | iso8601date2',
        callback(match, year, month, day) {
            return this.ymd(processYear(year), month - 1, +day)
        }
    },

    iso8601date4: {
        regex: RegExp('^' + reYear4withSign + '-' + reMonthlz + '-' + reDaylz),
        name: 'iso8601date4',
        callback(match, year, month, day) {
            return this.ymd(+year, month - 1, +day)
        }
    },

    gnuNoColon: {
        regex: RegExp('^t' + reHour24lz + reMinutelz, 'i'),
        name: 'gnunocolon',
        callback(match, hour, minute) {
            return this.time(+hour, +minute, 0, this.f)
        }
    },

    gnuDateShorter: {
        regex: RegExp('^' + reYear4 + '-' + reMonth),
        name: 'gnudateshorter',
        callback(match, year, month) {
            return this.ymd(+year, month - 1, 1)
        }
    },

    pgTextReverse: {
        // note: allowed years are from 32-9999
        // years below 32 should be treated as days in datefull
        regex: RegExp('^' + '(\\d{3,4}|[4-9]\\d|3[2-9])-(' + reMonthAbbr + ')-' + reDaylz, 'i'),
        name: 'pgtextreverse',
        callback(match, year, month, day) {
            return this.ymd(processYear(year), lookupMonth(month), +day)
        }
    },

    dateFull: {
        regex: RegExp('^' + reDay + '[ \\t.-]*' + reMonthText + '[ \\t.-]*' + reYear, 'i'),
        name: 'datefull',
        callback(match, day, month, year) {
            return this.ymd(processYear(year), lookupMonth(month), +day)
        }
    },

    dateNoDay: {
        regex: RegExp('^' + reMonthText + '[ .\\t-]*' + reYear4, 'i'),
        name: 'datenoday',
        callback(match, month, year) {
            return this.ymd(+year, lookupMonth(month), 1)
        }
    },

    dateNoDayRev: {
        regex: RegExp('^' + reYear4 + '[ .\\t-]*' + reMonthText, 'i'),
        name: 'datenodayrev',
        callback(match, year, month) {
            return this.ymd(+year, lookupMonth(month), 1)
        }
    },

    pgTextShort: {
        regex: RegExp('^(' + reMonthAbbr + ')-' + reDaylz + '-' + reYear, 'i'),
        name: 'pgtextshort',
        callback(match, month, day, year) {
            return this.ymd(processYear(year), lookupMonth(month), +day)
        }
    },

    dateNoYear: {
        regex: RegExp('^' + reMonthText + '[ .\\t-]*' + reDay + '[,.stndrh\\t ]*', 'i'),
        name: 'datenoyear',
        callback(match, month, day) {
            return this.ymd(this.y, lookupMonth(month), +day)
        }
    },

    dateNoYearRev: {
        regex: RegExp('^' + reDay + '[ .\\t-]*' + reMonthText, 'i'),
        name: 'datenoyearrev',
        callback(match, day, month) {
            return this.ymd(this.y, lookupMonth(month), +day)
        }
    },

    isoWeekDay: {
        regex: RegExp('^' + reYear4 + '-?W' + reWeekOfYear + '(?:-?([0-7]))?'),
        name: 'isoweekday | isoweek',
        callback(match, year, week, day) {
            day = day ? +day : 1

            if (!this.ymd(+year, 0, 1)) {
                return false
            }

            // get day of week for Jan 1st
            let dayOfWeek = new Date(this.y, this.m, this.d).getDay()

            // and use the day to figure out the offset for day 1 of week 1
            dayOfWeek = 0 - (dayOfWeek > 4 ? dayOfWeek - 7 : dayOfWeek)

            this.rd += dayOfWeek + ((week - 1) * 7) + day
        }
    },

    relativeText: {
        regex: RegExp('^(' + reReltextnumber + '|' + reReltexttext + ')' + reSpace + '(' + reReltextunit + ')', 'i'),
        name: 'relativetext',
        callback(match, relValue, relUnit) {
            // eslint-disable-next-line no-unused-vars
            const {amount, behavior} = lookupRelative(relValue)

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
                    this.resetTime()
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
        callback(match, signs, relValue, relUnit) {
            const minuses = signs.replace(/[^-]/g, '').length;

            let amount = +relValue * Math.pow(-1, minuses);

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
                    this.weekday = lookupWeekday(relUnit, 7)
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
        callback(match, dayText) {
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
        callback(match, relText) {
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
        callback(match, month) {
            return this.ymd(this.y, lookupMonth(month), this.d);
        }
    },

    tzCorrection: {
        regex: RegExp('^' + reTzCorrection, 'i'),
        name: 'tzcorrection',
        callback(tzCorrection) {
            return this.zone(processTzCorrection(tzCorrection));
        }
    },

    ago: {
        regex: /^ago/i,
        name: 'ago',
        callback() {
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
        // second instance of gnunocolon, without leading 't'
        // it's down here, because it is very generic (4 digits in a row)
        // thus conflicts with many rules above
        // only year4 should come afterwards
        regex: RegExp('^' + reHour24lz + reMinutelz, 'i'),
        name: 'gnunocolon',
        callback(match, hour, minute) {
            return this.time(+hour, +minute, 0, this.f);
        }
    },

    year4: {
        regex: RegExp('^' + reYear4),
        name: 'year4',
        callback(match, year) {
            this.y = +year;
            return true;
        }
    },

    whitespace: {
        regex: /^[ .,\t]+/,
        name: 'whitespace'
        // do nothing
    },

    any: {
        regex: /^[\s\S]+/,
        name: 'any',
        callback() {
            return false
        }
    }
};

let resultProto = {
    // date
    y: NaN,
    m: NaN,
    d: NaN,
    // time
    h: NaN,
    i: NaN,
    s: NaN,
    f: NaN,

    // relative shifts
    ry: 0,
    rm: 0,
    rd: 0,
    rh: 0,
    ri: 0,
    rs: 0,
    rf: 0,

    // weekday related shifts
    weekday: NaN,
    weekdayBehavior: 0,

    // first or last day of month
    // 0 none, 1 first, -1 last
    firstOrLastDayOfMonth: 0,

    // timezone correction in minutes
    z: NaN,

    // counters
    dates: 0,
    times: 0,
    zones: 0,

    // helper functions
    ymd(y, m, d) {
        if (this.dates > 0) {
            return false;
        }

        this.dates++;
        this.y = y;
        this.m = m;
        this.d = d;
        return true;
    },

    time(h, i, s, f) {
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

    resetTime() {
        this.h = 0;
        this.i = 0;
        this.s = 0;
        this.f = 0;
        this.times = 0;

        return true;
    },

    zone(minutes) {
        if (this.zones <= 1) {
            this.zones++;
            this.z = minutes;
            return true;
        }

        return false;
    },

    toDate(relativeTo) {
        if (this.dates && !this.times) {
            this.h = this.i = this.s = this.f = 0;
        }

        // fill holes
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

        // adjust special early
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
                // To make "this week" work, where the current day of week is a "sunday"
                if (dow === 0 && this.weekday !== 0) {
                    this.weekday = -6;
                }

                // To make "sunday this week" work, where the current day of week is not a "sunday"
                if (this.weekday === 0 && dow !== 0) {
                    this.weekday = 7;
                }

                this.d -= dow;
                this.d += this.weekday;
            } else {
                var diff = this.weekday - dow;

                // some PHP magic
                if ((this.rd < 0 && diff < 0) || (this.rd >= 0 && diff <= -this.weekdayBehavior)) {
                    diff += 7;
                }

                if (this.weekday >= 0) {
                    this.d += diff;
                } else {
                    this.d -= (7 - (Math.abs(this.weekday) - dow));
                }

                this.weekday = NaN;
            }
        }

        // adjust relative
        this.y += this.ry;
        this.m += this.rm;
        this.d += this.rd;

        this.h += this.rh;
        this.i += this.ri;
        this.s += this.rs;
        this.f += this.rf;

        this.ry = this.rm = this.rd = 0;
        this.rh = this.ri = this.rs = this.rf = 0;

        let result = new Date(relativeTo.getTime());
        // since Date constructor treats years <= 99 as 1900+
        // it can't be used, thus this weird way
        result.setFullYear(this.y, this.m, this.d);
        result.setHours(this.h, this.i, this.s, this.f);

        // note: this is done twice in PHP
        // early when processing special relatives
        // and late
        // to just one time action
        switch (this.firstOrLastDayOfMonth) {
            case 1:
                result.setDate(1);
                break;
            case -1:
                result.setMonth(result.getMonth() + 1, 0);
                break;
        }

        // adjust timezone
        if (!isNaN(this.z) && result.getTimezoneOffset() !== this.z) {
            result.setUTCFullYear(
                result.getFullYear(),
                result.getMonth(),
                result.getDate());

            result.setUTCHours(
                result.getHours(),
                result.getMinutes() + this.z,
                result.getSeconds(),
                result.getMilliseconds());
        }

        return result;
    }
};

function satechjobs_strtotime(str, now) {
    if (now == null) {
        now = Math.floor(Date.now() / 1000);
    }

    // the rule order is very fragile
    // as many formats are similar to others
    // so small change can cause
    // input misinterpretation
    const rules = [
        formats.yesterday,
        formats.now,
        formats.noon,
        formats.midnightOrToday,
        formats.tomorrow,
        formats.timestamp,
        formats.firstOrLastDay,
        formats.backOrFrontOf,
        // formats.weekdayOf, // not yet implemented
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
        // iso8601dateSlash needs to come before dateSlash
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
        // note: the two rules below
        // should always come last
        formats.whitespace,
        formats.any
    ];

    let result = Object.create(resultProto);

    while (str.length) {
        for (let i = 0, l = rules.length; i < l; i++) {
            const format = rules[i];

            const match = str.match(format.regex);

            if (match) {
                // care only about false results. Ignore other values
                // @ts-ignore
                if (format.callback && format.callback.apply(result, match) === false) {
                    return false
                }

                str = str.substr(match[0].length);
                break;
            }
        }
    }

    return Math.floor(result.toDate(new Date(now * 1000)) / 1000)
}

function satechjobs_nl2br(str = null, isXhtml) {
    // Some latest browsers when str is null return and unexpected null value
    if (str === null) {
        return '';
    }

    // Adjust comment to avoid issue on locutus.io display
    var breakTag = (isXhtml || typeof isXhtml === 'undefined') ? '<br ' + '/>' : '<br>';

    return (str + '')
        .replace(/(\r\n|\n\r|\r|\n)/g, breakTag + '$1');
}

function satechjobs_date(format, timestamp) {
    var jsdate, f
    // Keep this here (works, but for code commented-out below for file size reasons)
    // var tal= [];
    var txtWords = [
        'Sun', 'Mon', 'Tues', 'Wednes', 'Thurs', 'Fri', 'Satur',
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    // trailing backslash -> (dropped)
    // a backslash followed by any character (including backslash) -> the character
    // empty string -> empty string
    var formatChr = /\\?(.?)/gi;
    var formatChrCb = function (t, s) {
        return f[t] ? f[t]() : s
    };
    var _pad = function (n, c) {
        n = String(n);
        while (n.length < c) {
            n = '0' + n;
        }
        return n;
    };
    f = {
        // Day
        d: function () {
            // Day of month w/leading 0; 01..31
            return _pad(f.j(), 2);
        },
        D: function () {
            // Shorthand day name; Mon...Sun
            return f.l()
                .slice(0, 3);
        },
        j: function () {
            // Day of month; 1..31
            return jsdate.getDate();
        },
        l: function () {
            // Full day name; Monday...Sunday
            return txtWords[f.w()] + 'day';
        },
        N: function () {
            // ISO-8601 day of week; 1[Mon]..7[Sun]
            return f.w() || 7;
        },
        S: function () {
            // Ordinal suffix for day of month; st, nd, rd, th
            var j = f.j();
            var i = j % 10;
            if (i <= 3 && parseInt(String((j % 100) / 10), 10) === 1) {
                i = 0;
            }
            return ['st', 'nd', 'rd'][i - 1] || 'th'
        },
        w: function () {
            // Day of week; 0[Sun]..6[Sat]
            return jsdate.getDay()
        },
        z: function () {
            // Day of year; 0..365
            let a: any = new Date(f.Y(), f.n() - 1, f.j());
            let b: any = new Date(f.Y(), 0, 1);
            return Math.round((a - b) / 864e5);
        },

        // Week
        W: function () {
            // ISO-8601 week number
            let a: any = new Date(f.Y(), f.n() - 1, f.j() - f.N() + 3);
            let b: any = new Date(a.getFullYear(), 0, 4);
            return _pad(1 + Math.round((a - b) / 864e5 / 7), 2);
        },

        // Month
        F: function () {
            // Full month name; January...December
            return txtWords[6 + f.n()];
        },
        m: function () {
            // Month w/leading 0; 01...12
            return _pad(f.n(), 2);
        },
        M: function () {
            // Shorthand month name; Jan...Dec
            return f.F()
                .slice(0, 3);
        },
        n: function () {
            // Month; 1...12
            return jsdate.getMonth() + 1;
        },
        t: function () {
            // Days in month; 28...31
            return (new Date(f.Y(), f.n(), 0))
                .getDate();
        },

        // Year
        L: function () {
            // Is leap year?; 0 or 1
            var j = f.Y();
            // @ts-ignore
            return j % 4 === 0 & j % 100 !== 0 | j % 400 === 0;
        },
        o: function () {
            // ISO-8601 year
            var n = f.n();
            var W = f.W();
            var Y = f.Y();
            return Y + (n === 12 && W < 9 ? 1 : n === 1 && W > 9 ? -1 : 0);
        },
        Y: function () {
            // Full year; e.g. 1980...2010
            return jsdate.getFullYear();
        },
        y: function () {
            // Last two digits of year; 00...99
            return f.Y()
                .toString()
                .slice(-2);
        },

        // Time
        a: function () {
            // am or pm
            return jsdate.getHours() > 11 ? 'pm' : 'am';
        },
        A: function () {
            // AM or PM
            return f.a()
                .toUpperCase();
        },
        B: function () {
            // Swatch Internet time; 000..999
            var H = jsdate.getUTCHours() * 36e2;
            // Hours
            var i = jsdate.getUTCMinutes() * 60;
            // Minutes
            // Seconds
            var s = jsdate.getUTCSeconds();
            return _pad(Math.floor((H + i + s + 36e2) / 86.4) % 1e3, 3);
        },
        g: function () {
            // 12-Hours; 1..12
            return f.G() % 12 || 12;
        },
        G: function () {
            // 24-Hours; 0..23
            return jsdate.getHours();
        },
        h: function () {
            // 12-Hours w/leading 0; 01..12
            return _pad(f.g(), 2);
        },
        H: function () {
            // 24-Hours w/leading 0; 00..23
            return _pad(f.G(), 2);
        },
        i: function () {
            // Minutes w/leading 0; 00..59
            return _pad(jsdate.getMinutes(), 2);
        },
        s: function () {
            // Seconds w/leading 0; 00..59
            return _pad(jsdate.getSeconds(), 2);
        },
        u: function () {
            // Microseconds; 000000-999000
            return _pad(jsdate.getMilliseconds() * 1000, 6);
        },

        // Timezone
        e: function () {
            // Timezone identifier; e.g. Atlantic/Azores, ...
            // The following works, but requires inclusion of the very large
            // timezone_abbreviations_list() function.
            /*              return that.date_default_timezone_get();
             */
            var msg = 'Not supported (see source code of date() for timezone on how to add support)'
            throw new Error(msg);
        },
        I: function () {
            // DST observed?; 0 or 1
            // Compares Jan 1 minus Jan 1 UTC to Jul 1 minus Jul 1 UTC.
            // If they are not equal, then DST is observed.
            var a = new Date(f.Y(), 0);
            // Jan 1
            var c = Date.UTC(f.Y(), 0);
            // Jan 1 UTC
            let b: any = new Date(f.Y(), 6);
            // Jul 1
            // Jul 1 UTC
            let d: any = Date.UTC(f.Y(), 6);
            // @ts-ignore
            return ((a - c) !== (b - d)) ? 1 : 0;
        },
        O: function () {
            // Difference to GMT in hour format; e.g. +0200
            let tzo = jsdate.getTimezoneOffset();
            let a = Math.abs(tzo);
            return (tzo > 0 ? '-' : '+') + _pad(Math.floor(a / 60) * 100 + a % 60, 4);
        },
        P: function () {
            // Difference to GMT w/colon; e.g. +02:00
            var O = f.O();
            return (O.substr(0, 3) + ':' + O.substr(3, 2));
        },
        T: function () {
            return 'UTC';
        },
        Z: function () {
            // Timezone offset in seconds (-43200...50400)
            return -jsdate.getTimezoneOffset() * 60;
        },

        // Full Date/Time
        c: function () {
            // ISO-8601 date.
            return 'Y-m-d\\TH:i:sP'.replace(formatChr, formatChrCb);
        },
        r: function () {
            // RFC 2822
            return 'D, d M Y H:i:s O'.replace(formatChr, formatChrCb);
        },
        U: function () {
            // Seconds since UNIX epoch
            return jsdate / 1000 | 0;
        }
    };

    let _date = function (format, timestamp) {
        jsdate = (timestamp === undefined ? new Date() // Not provided
                : (timestamp instanceof Date) ? new Date(timestamp) // JS Date()
                    : new Date(timestamp * 1000) // UNIX timestamp (auto-convert to int)
        )
        return format.replace(formatChr, formatChrCb);
    }

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
    // http://kevin.vanzonneveld.net
    // +   original by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: stripcslashes("\\f\\o\\o\\[ \\]");
    // *     returns 1: 'foo[ ]'

    // @ts-ignore
    let target = '', i = 0, sl = 0, s = '', next = '', hex: RegExpExecArray = '', oct: RegExpExecArray = '',
        hex2DigMax = /[\dA-Fa-f]{1,2}/, rest = '',
        seq = '',
        oct3DigMaxs = /([0-7]{1,3})((\\[0-7]{1,3})*)/, oct3DigMax = /(\\([0-7]{1,3}))*/g, escOctGrp = [];

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
            case 'x': // Hex (not used in addcslashes)
                rest = str.slice(i + 2);
                if (rest.search(hex2DigMax) !== -1) { // C accepts hex larger than 2 digits (per http://www.php.net/manual/en/function.stripcslashes.php#34041 ), but not PHP
                    hex = (hex2DigMax).exec(rest);
                    i += hex.length; // Skip over hex
                    // @ts-ignore
                    target += String.fromCharCode(parseInt(hex, 16));
                    break;
                }
            // Fall-through
            default: // Up to 3 digit octal in PHP, but we may have created a larger one in addcslashes
                rest = str.slice(i + 2);
                if (rest.search(oct3DigMaxs) !== -1) { // C accepts hex larger than 2 digits (per http://www.php.net/manual/en/function.stripcslashes.php#34041 ), but not PHP
                    oct = (oct3DigMaxs).exec(rest);
                    i += oct[1].length; // Skip over first octal
                    // target += String.fromCharCode(parseInt(oct[1], 8)); // Sufficient for UTF-16 treatment

                    // Interpret int here as UTF-8 octet(s) instead, produce non-character if none
                    rest = str.slice(i + 2); // Get remainder after the octal (still need to add 2, since before close of iterating loop)
                    seq = '';

                    if ((escOctGrp = oct3DigMax.exec(rest)) !== null) {
                        seq += '%' + parseInt(escOctGrp[2], 8).toString(16);
                    }
                    /* infinite loop
                    while ((escOctGrp = oct3DigMax.exec(rest)) !== null) {
                        seq += '%'+parseInt(escOctGrp[2], 8).toString(16);
                    }

                    dl('stripcslashes');
                    alert(
                        stripcslashes('\\343\\220\\201')
                    )
                    */

                    try {
                        target += decodeURIComponent(seq);
                    } catch (e) { // Bad octal group
                        target += '\uFFFD'; // non-character
                    }

                    break;
                }
                target += next;
                break;
        }
        ++i; // Skip special character "next" in switch
    }

    return target;
}

function satechjobs_stripslashes(str) {
    //       discuss at: https://locutus.io/php/stripslashes/
    //      original by: Kevin van Zonneveld (https://kvz.io)
    //      improved by: Ates Goral (https://magnetiq.com)
    //      improved by: marrtins
    //      improved by: rezna
    //         fixed by: Mick@el
    //      bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
    //      bugfixed by: Brett Zamir (https://brett-zamir.me)
    //         input by: Rick Waldron
    //         input by: Brant Messenger (https://www.brantmessenger.com/)
    // reimplemented by: Brett Zamir (https://brett-zamir.me)
    //        example 1: stripslashes('Kevin\'s code')
    //        returns 1: "Kevin's code"
    //        example 2: stripslashes('Kevin\\\'s code')
    //        returns 2: "Kevin\'s code"

    return (str + '')
        .replace(/\\(.?)/g, function (s, n1) {
            switch (n1) {
                case '\\':
                    return '\\'
                case '0':
                    return '\u0000'
                case '':
                    return ''
                default:
                    return n1
            }
        })
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
    //  discuss at: https://locutus.io/php/count/
    // original by: Kevin van Zonneveld (https://kvz.io)
    //    input by: Waldo Malqui Silva (https://waldo.malqui.info)
    //    input by: merabi
    // bugfixed by: Soren Hansen
    // bugfixed by: Olivier Louvignes (https://mg-crea.com/)
    // improved by: Brett Zamir (https://brett-zamir.me)
    //   example 1: count([[0,0],[0,-4]], 'COUNT_RECURSIVE')
    //   returns 1: 6
    //   example 2: count({'one' : [1,2,3,4,5]}, 'COUNT_RECURSIVE')
    //   returns 2: 6

    var key;
    var cnt = 0;

    if (mixedVar === null || typeof mixedVar === 'undefined') {
        return 0;
    } else if (mixedVar.constructor !== Array && mixedVar.constructor !== Object) {
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
            cnt++
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

    // @ts-ignore
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
        ra = Object.prototype.toString.call(r) === '[object Array]'
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