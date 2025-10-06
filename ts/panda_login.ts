document.addEventListener('DOMContentLoaded', () => {
    const setStyles = (selector: string, styles: Partial<CSSStyleDeclaration>) => {
        document.querySelectorAll<HTMLElement>(selector).forEach(el => {
            Object.assign(el.style, styles);
        });
    };

    const loginInput = document.getElementById('login') as HTMLInputElement | null;
    const passwordInput = document.getElementById('password') as HTMLInputElement | null;

    if (loginInput) {
        loginInput.addEventListener('focus', () => {
            setStyles('.handl', {
                transform: 'rotate(0deg)',
                bottom: '140px',
                left: '50px',
                height: '45px',
                width: '35px'
            });
            setStyles('.handr', {
                transform: 'rotate(0deg)',
                bottom: '185px',
                left: '250px',
                height: '45px',
                width: '35px'
            });
            setStyles('.eyeball1', {
                top: '20px',
                left: '13px'
            });
            setStyles('.eyeball2', {
                top: '20px',
                left: '8px'
            });
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('focus', () => {
            setStyles('.eyeball1', {
                top: '10px',
                left: '10px'
            });
            setStyles('.eyeball2', {
                top: '10px',
                left: '10px'
            });
            setStyles('.handl', {
                transform: 'rotate(-150deg)',
                bottom: '215px',
                left: '105px',
                height: '90px',
                width: '40px'
            });
            setStyles('.handr', {
                transform: 'rotate(150deg)',
                bottom: '308px',
                left: '192px',
                height: '90px',
                width: '40px'
            });
        });
    }

    const blurHandler = () => {
        setTimeout(() => {
            setStyles('.handl', {
                transform: 'rotate(0deg)',
                bottom: '140px',
                left: '50px',
                height: '45px',
                width: '35px'
            });
            setStyles('.handr', {
                transform: 'rotate(0deg)',
                bottom: '185px',
                left: '250px',
                height: '45px',
                width: '35px'
            });
            setStyles('.eyeball1, .eyeball2', {
                top: '15px',
                left: '10px'
            });
        }, 200);
    };

    if (loginInput) loginInput.addEventListener('blur', blurHandler);
    if (passwordInput) passwordInput.addEventListener('blur', blurHandler);
});