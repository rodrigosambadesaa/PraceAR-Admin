import assert from "node:assert/strict";
import test from "node:test";

import bigInteger from "big-integer";

globalThis.bigInt = bigInteger;

const {
    ATTACK_SCENARIOS,
    estimateBruteForceAttackTimes,
    formatArbitraryIntegerScientific,
    formatAttackDuration,
} = await import("../laravel-docker/resources/ts/admin/js/helpers/password_attack_estimator.js");

test("calcula exactamente espacios de búsqueda con big-integer", () => {
    const result = estimateBruteForceAttackTimes(87, 1024);
    const expected = bigInteger(87).pow(1024);

    assert.ok(result.searchSpace.equals(expected));
    assert.ok(result.averageGuesses.equals(result.searchSpace.add(1).divide(2)));
    assert.equal(result.estimates.length, ATTACK_SCENARIOS.length);
    assert.ok(result.searchSpace.toString().length > 1_900);
});

test("mantiene precisión arbitraria y usa notación científica", () => {
    assert.equal(formatArbitraryIntegerScientific("0"), "0 × 10^0");
    assert.equal(formatArbitraryIntegerScientific("42"), "4,20 × 10^1");
    assert.equal(formatArbitraryIntegerScientific("9999"), "1,00 × 10^4");
    assert.match(
        formatArbitraryIntegerScientific(bigInteger(87).pow(1024)),
        /^\d,\d{2} × 10\^\d+$/,
    );
});

test("los escenarios más rápidos reducen el tiempo estimado", () => {
    const { estimates } = estimateBruteForceAttackTimes(87, 64);

    for (let index = 1; index < estimates.length; index += 1) {
        assert.ok(estimates[index]!.seconds.lesser(estimates[index - 1]!.seconds));
    }
    assert.match(estimates.at(-1)!.formattedDuration, /años/);
    assert.match(formatAttackDuration("31557600"), /años/);
});

test("rechaza entradas que no sean enteros seguros válidos", () => {
    assert.throws(() => estimateBruteForceAttackTimes(1, 16), RangeError);
    assert.throws(() => estimateBruteForceAttackTimes(87, 0), RangeError);
    assert.throws(() => formatArbitraryIntegerScientific({}), TypeError);
});
