declare module "*js/helpers/clean_input.js" {
    export function limpiarInput(input: string): string;
    export function limpiarTextarea(textarea: string): string;
}

declare module "*js/helpers/verify_strong_password.js" {
    export function verifyStrongPassword(password: string): boolean;
    export function haSidoFiltradaEnBrechas(password: string): Promise<boolean>;
    export function contrasenhaSimilarAUsuario(contrasenha: string, usuario: string | string[]): boolean;
    export function tieneSecuenciasNumericasInseguras(contrasenha: string): boolean;
    export function tieneSecuenciasAlfabeticasInseguras(contrasenha: string): boolean;
    export function tieneSecuenciasDeCaracteresEspecialesInseguras(contrasenha: string): boolean;
    export function tieneEspaciosAlPrincipioOAlFinal(contrasenha: string): boolean;
}

declare module "*js/constants.js" {
    export const UNITY_TYPE: Record<string, string>;
}
