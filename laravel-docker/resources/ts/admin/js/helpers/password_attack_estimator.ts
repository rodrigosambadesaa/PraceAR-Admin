import type bigInteger from "big-integer";

type ArbitraryInteger = ReturnType<typeof bigInteger>;
type ArbitraryIntegerFactory = typeof bigInteger;

const SECONDS_PER_YEAR = "31557600";

export const ATTACK_SCENARIOS = Object.freeze([
    Object.freeze({
        id: "online-throttled",
        label: "En línea con limitación",
        description: "Servicio con bloqueo y rate limiting",
        guessesPerSecond: "10",
    }),
    Object.freeze({
        id: "online-unthrottled",
        label: "En línea sin limitación",
        description: "Servicio expuesto sin controles efectivos",
        guessesPerSecond: "1000",
    }),
    Object.freeze({
        id: "offline-slow-hash",
        label: "Offline contra hash lento",
        description: "Hash moderno con coste de memoria y CPU",
        guessesPerSecond: "100000",
    }),
    Object.freeze({
        id: "offline-fast-hash",
        label: "GPU/ASIC contra hash rápido",
        description: "Hash robado débil o sin coste adaptativo",
        guessesPerSecond: "100000000000000",
    }),
    Object.freeze({
        id: "extreme-distributed",
        label: "Ataque distribuido extremo",
        description: "Capacidad teórica de 10¹⁸ intentos/s",
        guessesPerSecond: "1000000000000000000",
    }),
]);

function integerFactory(): ArbitraryIntegerFactory {
    const factory = globalThis.bigInt as ArbitraryIntegerFactory | undefined;
    if (typeof factory !== "function" || typeof factory.isInstance !== "function") {
        throw new Error("La biblioteca de enteros arbitrarios no está disponible.");
    }

    return factory;
}

function assertPositiveSafeInteger(value: number, name: string): void {
    if (!Number.isSafeInteger(value) || value < 1) {
        throw new RangeError(`${name} debe ser un entero positivo seguro.`);
    }
}

function arbitraryInteger(value: unknown): ArbitraryInteger {
    const factory = integerFactory();

    if (factory.isInstance(value)) {
        return value as ArbitraryInteger;
    }
    if (typeof value === "string" && /^-?\d+$/.test(value)) {
        return factory(value);
    }
    if (typeof value === "number" && Number.isSafeInteger(value)) {
        return factory(String(value));
    }

    throw new TypeError("El valor debe ser un entero compatible con big-integer.");
}

function divideRoundingUp(
    dividend: ArbitraryInteger,
    divisor: ArbitraryInteger,
): ArbitraryInteger {
    return dividend.add(divisor).subtract(1).divide(divisor);
}

export function formatArbitraryIntegerScientific(
    value: unknown,
    significantDigits = 3,
): string {
    const integer = arbitraryInteger(value);
    assertPositiveSafeInteger(significantDigits, "significantDigits");

    if (integer.isZero()) {
        return "0 × 10^0";
    }

    const isNegative = integer.isNegative();
    const digits = integer.abs().toString();
    let exponent = digits.length - 1;
    let significant = digits.slice(0, significantDigits).padEnd(significantDigits, "0");
    const nextDigit = digits.charAt(significantDigits);

    if (nextDigit >= "5") {
        significant = arbitraryInteger(significant).add(1).toString();
        if (significant.length > significantDigits) {
            exponent += 1;
            significant = significant.slice(0, significantDigits);
        }
        significant = significant.padStart(significantDigits, "0");
    }

    const decimalPart = significant.slice(1);
    const coefficient = decimalPart === ""
        ? significant
        : `${significant.charAt(0)},${decimalPart}`;

    return `${isNegative ? "-" : ""}${coefficient} × 10^${exponent}`;
}

export function formatAttackDuration(seconds: unknown): string {
    const secondsInteger = arbitraryInteger(seconds);
    if (secondsInteger.isNegative()) {
        throw new RangeError("Los segundos no pueden ser negativos.");
    }

    const scientificSeconds = formatArbitraryIntegerScientific(secondsInteger);
    const secondsPerYear = arbitraryInteger(SECONDS_PER_YEAR);
    if (secondsInteger.lesser(secondsPerYear)) {
        return `${scientificSeconds} s`;
    }

    const years = divideRoundingUp(secondsInteger, secondsPerYear);
    return `${formatArbitraryIntegerScientific(years)} años (${scientificSeconds} s)`;
}

export function estimateBruteForceAttackTimes(
    alphabetSize: number,
    passwordLength: number,
) {
    assertPositiveSafeInteger(alphabetSize, "alphabetSize");
    assertPositiveSafeInteger(passwordLength, "passwordLength");

    if (alphabetSize < 2) {
        throw new RangeError("alphabetSize debe ser al menos 2.");
    }

    const searchSpace = arbitraryInteger(String(alphabetSize)).pow(passwordLength);
    const averageGuesses = divideRoundingUp(searchSpace, arbitraryInteger("2"));
    const estimates = ATTACK_SCENARIOS.map((scenario) => {
        const guessesPerSecond = arbitraryInteger(scenario.guessesPerSecond);
        const seconds = divideRoundingUp(averageGuesses, guessesPerSecond);

        return Object.freeze({
            ...scenario,
            seconds,
            formattedRate: `${formatArbitraryIntegerScientific(guessesPerSecond)} intentos/s`,
            formattedDuration: formatAttackDuration(seconds),
        });
    });

    return Object.freeze({
        searchSpace,
        averageGuesses,
        formattedSearchSpace: formatArbitraryIntegerScientific(searchSpace),
        formattedAverageGuesses: formatArbitraryIntegerScientific(averageGuesses),
        estimates: Object.freeze(estimates),
    });
}
