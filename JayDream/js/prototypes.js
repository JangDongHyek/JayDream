// int일경우 자동으로 컴마가 붙는 프로토타입
Number.prototype.format = function (n, x) {
    var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\.' : '$') + ')';
    return this.toFixed(Math.max(0, ~~n)).replace(new RegExp(re, 'g'), '$&,');
};

// Date 타입의 변수 자동으로 포맷팅 YYYY-MM-DD 로 반환됌
Date.prototype.format = function () {
    const year = this.getFullYear();
    const month = String(this.getMonth() + 1).padStart(2, '0');
    const day = String(this.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
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

String.prototype.formatDate = function(options = { time: false, type: '-', simple: true, second : false}) {
    // 기본 옵션 설정
    const defaultOptions = { time: false, type: '-', simple: true , second : false,};
    const opts = Object.assign({}, defaultOptions, options); // 사용자 옵션 병합

    // 날짜와 시간 분리
    let [datePart, timePart] = this.split(' ');

    // 날짜를 '-' 기준으로 분리
    let parts = datePart.split('-');

    // simple 옵션이 true이면 연도를 두 자리로 변환
    if (opts.simple) {
        parts[0] = parts[0].slice(2); // "2024" -> "24"
    }

    // 변환된 날짜 문자열
    let formattedDate = parts.join(opts.type);

    // time 옵션이 true이면 시간 추가
    if (opts.time && timePart) {
        let timeParts = timePart.split(':');

        if (!opts.second && timeParts.length >= 2) {
            // 초 제외: HH:mm
            formattedDate += ` ${timeParts[0]}:${timeParts[1]}`;
        } else if (opts.second && timeParts.length === 3) {
            // 초 포함: HH:mm:ss
            formattedDate += ` ${timeParts.join(':')}`;
        }
    }



    return formattedDate;
};