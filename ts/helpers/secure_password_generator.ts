type PasswordStats = {
    uppercase: number;
    lowercase: number;
    digits: number;
    special: number;
    entropy: string;
    resistanceTimeToBruteForceAttack?: string;
};

export class SecurePasswordGenerator {
    private static NUM_SEQS: string[] = SecurePasswordGenerator.computeNumSeqs();
    private static ALPHA_SEQS: string[] = SecurePasswordGenerator.computeAlphaSeqs();
    private static SPECIAL_SEQS: string[] = [
        "!@#$%^&*()_+", "-=", "[]", ";'", ",./", "{}", ":\"", "<>?"
    ];

    private static computeNumSeqs(): string[] {
        const nums = "0123456789", rev = nums.split("").reverse().join("");
        let arr: string[] = [];
        for (let l = 2; l <= 5; l++) {
            for (let i = 0; i <= nums.length - l; i++) arr.push(nums.slice(i, i + l), rev.slice(i, i + l));
        }
        arr.push(...["159", "951", "753", "357", "147", "741", "369", "963", "258", "852"]);
        return arr;
    }

    private static computeAlphaSeqs(): string[] {
        const abc = "abcdefghijklmnopqrstuvwxyz", rev = abc.split("").reverse().join("");
        const filas = ["qwertyuiop", "asdfghjklñ", "zxcvbnm"];
        let arr: string[] = [];
        for (let l = 2; l <= 5; l++) {
            for (let i = 0; i <= abc.length - l; i++) arr.push(abc.slice(i, i + l), rev.slice(i, i + l));
        }
        for (const f of filas) {
            const fr = f.split("").reverse().join("");
            for (let l = 2; l <= f.length; l++) {
                for (let i = 0; i <= f.length - l; i++) arr.push(f.slice(i, i + l), fr.slice(i, i + l));
            }
        }
        arr.push(...["qaz", "wsx", "edc", "rfv", "tgb", "yhn", "ujm",
            "qazwsx", "wsxedc", "edcrfv", "rfvtgb", "tgbnhy", "yhnujm"]);
        arr.push(...["zxc", "vbn", "mnb", "poi", "lkj", "hgf", "dsq"]);
        return arr;
    }

    static verifyStrongPassword(password: string): boolean {
        if (password.length < 16 || password.length > 1024) return false;
        if (!/[a-z]/.test(password)) return false;
        if (!/[A-Z]/.test(password)) return false;
        if (!/[0-9]/.test(password)) return false;
        let especiales = new Set<string>();
        for (const c of password) if ("!@#$%^&*()-_=+[]{}|;:,.<>?/".includes(c)) especiales.add(c);
        if (especiales.size < 3) return false;
        if (password.startsWith(" ") || password.endsWith(" ")) return false;
        if (this.NUM_SEQS.some(seq => password.includes(seq))) return false;
        if (this.ALPHA_SEQS.some(seq => password.toLowerCase().includes(seq))) return false;
        for (const seq of this.SPECIAL_SEQS) {
            for (let i = 0; i <= password.length - seq.length; i++) {
                if (seq.includes(password.substring(i, i + seq.length))) return false;
            }
        }
        return true;
    }

    private static shuffleString(str: string): string {
        const arr = str.split("");
        for (let i = arr.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [arr[i], arr[j]] = [arr[j], arr[i]];
        }
        return arr.join("");
    }

    static generarSubcontrasena(length: number): string {
        const mayus = "ABCDEFGHIJKLMNOPQRSTUVWXYZ", minus = "abcdefghijklmnopqrstuvwxyz", nums = "0123456789";
        const esp = "!@#$%^&*()-_=+[]{}|;:,.<>?/", todos = mayus + minus + nums + esp;
        while (true) {
            let arr: string[] = [
                mayus[Math.floor(Math.random() * mayus.length)],
                minus[Math.floor(Math.random() * minus.length)],
                nums[Math.floor(Math.random() * nums.length)]
            ];
            let usados = new Set<string>();
            while (usados.size < 3) usados.add(esp[Math.floor(Math.random() * esp.length)]);
            arr.push(...usados);
            while (arr.length < length) arr.push(todos[Math.floor(Math.random() * todos.length)]);
            let pwd = this.shuffleString(arr.join(""));
            for (let i = 0; i < 10; i++) {
                if (this.verifyStrongPassword(pwd)) return pwd;
                pwd = this.shuffleString(pwd);
            }
        }
    }

    static generarPassword(length: number): string {
        if (length > 256) {
            const partes = length > 512 ? 4 : 2;
            const longitudParte = Math.floor(length / partes), resto = length % partes;
            while (true) {
                let pwd = "";
                for (let i = 0; i < partes; i++) {
                    let l = longitudParte + (i === partes - 1 ? resto : 0);
                    pwd += this.generarSubcontrasena(l);
                }
                for (let i = 0; i < 10; i++) {
                    if (this.verifyStrongPassword(pwd)) return pwd;
                    pwd = this.shuffleString(pwd);
                }
            }
        } else {
            return this.generarSubcontrasena(length);
        }
    }

    static getStats(password: string): PasswordStats {
        const uppercase = (password.match(/[A-Z]/g) || []).length;
        const lowercase = (password.match(/[a-z]/g) || []).length;
        const digits = (password.match(/[0-9]/g) || []).length;
        const special = (password.match(/[^A-Za-z0-9]/g) || []).length;
        const entropy = (lowercase * 5 + uppercase * 6 + digits * 7 + special * 8) + " bits";

        // Usar BigInt y exponentiación para evitar overflow de Number/Infinity
        const totalCombinations = (26n ** BigInt(lowercase + uppercase)) *
            (10n ** BigInt(digits)) *
            (32n ** BigInt(special));

        const attemptsPerSecond = 1000n;
        const secondsInYear = 31557600n; // 365.25 * 24 * 3600
        const yearsToCrack = totalCombinations / (attemptsPerSecond * secondsInYear);

        let resistanceTimeToBruteForceAttack: string;
        if (yearsToCrack < 1_000n) {
            resistanceTimeToBruteForceAttack = yearsToCrack.toString() + " años";
        } else if (yearsToCrack < 1_000_000n) {
            resistanceTimeToBruteForceAttack = (yearsToCrack / 1_000n).toString() + " mil años";
        } else {
            const millones = yearsToCrack / 1_000_000n;
            resistanceTimeToBruteForceAttack = millones.toString() + " millones de años";
        }

        return { uppercase, lowercase, digits, special, entropy, resistanceTimeToBruteForceAttack };
    }
}