class JayDreamPrototype {
    constructor(jd) {
        this.jd = jd;
    }

    format(n) {
        if (!/^-?\d+(\.\d+)?$/.test(String(n))) {
            throw new Error('formatNumber: 숫자만 입력 가능합니다.');
        }

        const number = Number(n);
        const re = '\\d(?=(\\d{3})+$)';
        return number.toFixed(0).replace(new RegExp(re, 'g'), '$&,');
    }

    formatBytes(n,...args) {
        if (!/^-?\d+(\.\d+)?$/.test(String(n))) {
            throw new Error('formatNumber: 숫자만 입력 가능합니다.');
        }

        let defaults = {decimals: 2, inputUnit: 'byte'};
        let {decimals, inputUnit} = this.jd.lib.args(defaults,...args);

        // 입력 단위를 바이트로 변환하는 배율
        const units = {
            'byte': 1,
            'bytes': 1,
            'kb': 1024,
            'mb': 1024 * 1024,
            'gb': 1024 * 1024 * 1024,
            'tb': 1024 * 1024 * 1024 * 1024
        };

        const unitKey = inputUnit.toLowerCase();
        if (!units[unitKey]) {
            throw new Error('formatBytes: 지원하지 않는 단위입니다. (byte, kb, mb, gb, tb)');
        }

        // 입력값을 바이트로 변환
        const bytes = value * units[unitKey];

        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const dm = decimals < 0 ? 0 : decimals;

        // 적절한 단위 결정
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        const size = parseFloat((bytes / Math.pow(k, i)).toFixed(dm));

        return `${size} ${sizes[i]}`;
    }

    formatDate(...args) {
        const def = {date: new Date(), format: 'yyyy-mm-dd'};
        const {date, format} = this.jd.lib.args(def, ...args);

        let dateObj;

        // Date 객체 변환
        if (date instanceof Date) {
            dateObj = date;
        } else if (typeof date === 'string' || typeof date === 'number') {
            // 문자열이나 숫자(timestamp)를 Date로 변환
            dateObj = new Date(date);
        } else {
            throw new Error('formatDate: Date 객체, 문자열, timestamp만 입력 가능합니다.');
        }

        // 유효한 날짜인지 확인
        if (isNaN(dateObj.getTime())) {
            throw new Error('formatDate: 유효하지 않은 날짜입니다.');
        }

        const yyyy = dateObj.getFullYear();
        const yy = String(yyyy).slice(-2);
        const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
        const dd = String(dateObj.getDate()).padStart(2, '0');
        const hh = String(dateObj.getHours()).padStart(2, '0');
        const mi = String(dateObj.getMinutes()).padStart(2, '0');
        const ss = String(dateObj.getSeconds()).padStart(2, '0');

        return format
            .replace(/yyyy/g, yyyy)
            .replace(/yy/g, yy)
            .replace(/mm/g, mm)
            .replace(/dd/g, dd)
            .replace(/hh/g, hh)
            .replace(/mi/g, mi)
            .replace(/ss/g, ss);
    }

}