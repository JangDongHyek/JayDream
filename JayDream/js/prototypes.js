// int일경우 자동으로 컴마가 붙는 프로토타입
Number.prototype.format = function (n, x) {
    var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\.' : '$') + ')';
    return this.toFixed(Math.max(0, ~~n)).replace(new RegExp(re, 'g'), '$&,');
};



//배열을 튜플형식으로 변환하는
Array.prototype.tuple = function () {
    return `('${this.join("','")}')`;
};

/**
 * 숫자(바이트 단위)를 읽기 쉬운 크기 단위로 변환하는 프로토타입
 * @param {number} decimals - 소수점 자릿수 (기본값: 2)
 * @returns {string} 읽기 쉬운 크기 단위 (예: "408 KB", "3.5 MB")
 */
Number.prototype.formatBytes = function (decimals = 2) {
    if (this === 0) return '0 Bytes';

    const k = 1024; // 1 KB = 1024 Bytes
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const dm = decimals < 0 ? 0 : decimals;

    // 단위 결정
    const i = Math.floor(Math.log(this) / Math.log(k));
    const size = parseFloat((this / Math.pow(k, i)).toFixed(dm));

    return `${size} ${sizes[i]}`;
};



/**
 * 문자열에서 숫자만 추출하는 프로토타입
 * 예: "010-1234-1234" → "01012341234"
 */
String.prototype.formatOnlyNumber = function () {
    return this.replace(/\D/g, '');
};

// DATE
Date.prototype.format = function(fmt = 'yyyy-mm-dd') {
    const yyyy = this.getFullYear();
    const yy = String(yyyy).slice(-2);
    const mm = String(this.getMonth() + 1).padStart(2, '0');
    const dd = String(this.getDate()).padStart(2, '0');
    const hh = String(this.getHours()).padStart(2, '0');
    const mi = String(this.getMinutes()).padStart(2, '0');
    const ss = String(this.getSeconds()).padStart(2, '0');

    return fmt
        .replace(/yyyy/g, yyyy)
        .replace(/yy/g, yy)
        .replace(/mm/g, mm)
        .replace(/dd/g, dd)
        .replace(/hh/g, hh)
        .replace(/mi/g, mi)
        .replace(/ss/g, ss);
};

Date.prototype.lastDay = function () {
    const nextMonth = new Date(this.getFullYear(), this.getMonth() + 1, 1);
    nextMonth.setDate(0);

    const yyyy = nextMonth.getFullYear();
    const mm = String(nextMonth.getMonth() + 1).padStart(2, '0');
    const dd = String(nextMonth.getDate()).padStart(2, '0');

    return `${yyyy}-${mm}-${dd}`;
};

String.prototype.lastDay = function () {
    const date = new Date(this);
    if (isNaN(date)) {
        throw new Error(`Invalid date string: ${this}`);
    }
    return date.lastDay();
};

Date.prototype.firstDay = function () {
    const yyyy = this.getFullYear();
    const mm = String(this.getMonth() + 1).padStart(2, '0');
    return `${yyyy}-${mm}-01`;
};

String.prototype.firstDay = function () {
    const date = new Date(this);
    if (isNaN(date)) {
        throw new Error(`Invalid date string: ${this}`);
    }
    return date.firstDay();
};

String.prototype.formatDate = function(fmt = 'yyyy-mm-dd') {
    const date = new Date(this);
    if (isNaN(date.getTime())) {
        throw new Error(`Invalid date string: "${this}"`);
    }
    return date.format(fmt);
};