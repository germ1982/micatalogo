/* Mi Catálogo - comportamiento general */
window.Catalogo = (function () {
    'use strict';

    /* ---------- Mensajes (SweetAlert) ---------- */

    function mensaje(opciones) {
        if (!window.Swal) {
            alert((opciones.title || '') + '\n' + (opciones.html || '').replace(/<[^>]+>/g, ''));
            return Promise.resolve({ isConfirmed: true });
        }
        var estilo = getComputedStyle(document.documentElement);
        return Swal.fire(Object.assign({
            confirmButtonText: 'Aceptar',
            background: estilo.getPropertyValue('--superficie').trim() || undefined,
            color: estilo.getPropertyValue('--texto').trim() || undefined,
            confirmButtonColor: estilo.getPropertyValue('--primario').trim() || undefined
        }, opciones));
    }

    /* Los data-confirm de Yii (borrar en las grillas, etc.) usan SweetAlert en lugar del confirm() del navegador */
    if (window.yii) {
        yii.confirm = function (texto, ok, cancelar) {
            mensaje({
                icon: 'warning', title: 'Confirmá', text: texto, showCancelButton: true,
                confirmButtonText: 'Sí, continuar', cancelButtonText: 'Cancelar', focusCancel: true
            }).then(function (r) {
                if (r.isConfirmed) { ok && ok(); } else { cancelar && cancelar(); }
            });
        };
    }

    /* ---------- Filtros del catálogo: al cambiar categoría u orden se aplica solo ---------- */

    document.addEventListener('change', function (e) {
        var form = e.target.closest('[data-filtros]');
        if (form && e.target.tagName === 'SELECT') {
            form.submit();
        }
    });

    /* ---------- Compartir ---------- */

    function copiar(texto) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(texto).catch(function () { return copiarViejo(texto); });
        }
        return copiarViejo(texto);
    }

    // http (sin https) en el celular, o portapapeles bloqueado: método viejo
    function copiarViejo(texto) {
        var campo = document.createElement('textarea');
        campo.value = texto;
        campo.setAttribute('readonly', '');
        campo.style.position = 'fixed';
        campo.style.opacity = '0';
        document.body.appendChild(campo);
        campo.select();
        var ok = document.execCommand('copy');
        document.body.removeChild(campo);
        return ok ? Promise.resolve() : Promise.reject();
    }

    document.addEventListener('click', function (e) {
        var boton = e.target.closest('[data-copiar]');
        if (!boton) { return; }
        copiar(boton.getAttribute('data-copiar')).then(function () {
            mensaje({ icon: 'success', title: 'Link copiado', text: 'Ya podés pegarlo donde quieras.', timer: 1800, showConfirmButton: false });
        }, function () {
            mensaje({ icon: 'info', title: 'Copiá el link', text: boton.getAttribute('data-copiar') });
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        if (!navigator.share) { return; }
        document.querySelectorAll('[data-compartir-nativo]').forEach(function (b) { b.classList.remove('d-none'); });
    });

    document.addEventListener('click', function (e) {
        var boton = e.target.closest('[data-compartir-nativo]');
        if (!boton || !navigator.share) { return; }
        navigator.share({
            title: boton.getAttribute('data-titulo'),
            text: boton.getAttribute('data-texto'),
            url: boton.getAttribute('data-url')
        }).catch(function () { /* el usuario canceló */ });
    });

    /* ---------- Galería de la ficha ---------- */

    document.addEventListener('click', function (e) {
        var boton = e.target.closest('[data-galeria-foto]');
        if (!boton) { return; }
        var galeria = boton.closest('[data-galeria]');
        galeria.querySelector('[data-galeria-principal]').src = boton.getAttribute('data-galeria-foto');
        galeria.querySelectorAll('[data-galeria-foto]').forEach(function (b) { b.classList.toggle('activa', b === boton); });
    });

    /* ---------- Vista previa de las fotos elegidas ---------- */

    document.addEventListener('change', function (e) {
        if (!e.target.matches('[data-vista-previa]')) { return; }
        var destino = document.querySelector(e.target.getAttribute('data-vista-previa'));
        if (!destino) { return; }
        destino.innerHTML = '';
        Array.prototype.forEach.call(e.target.files || [], function (archivo) {
            if (!/^image\//.test(archivo.type)) { return; }
            var caja = document.createElement('div');
            caja.className = 'foto-cargada nueva';
            var img = document.createElement('img');
            img.src = URL.createObjectURL(archivo);
            img.alt = '';
            caja.appendChild(img);
            destino.appendChild(caja);
        });
    });

    return { mensaje: mensaje };
})();
