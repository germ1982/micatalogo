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

    /* ---------- Filtros del catálogo: al cambiar categoría, orden o Mostrar se aplica solo ---------- */

    document.addEventListener('change', function (e) {
        var form = e.target.closest('[data-filtros]');
        if (form && (e.target.tagName === 'SELECT' || e.target.type === 'checkbox')) {
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

    /* ---------- Precio fijo en pesos o atado al dólar (formulario de publicación) ---------- */

    function numero(texto) {
        texto = (texto || '').replace(/[$\s]/g, '');
        if (texto.indexOf(',') >= 0) { texto = texto.replace(/\./g, '').replace(',', '.'); }
        else if (/^\d{1,3}(\.\d{3})+$/.test(texto)) { texto = texto.replace(/\./g, ''); }
        var n = parseFloat(texto);
        return isNaN(n) ? null : n;
    }

    function actualizarPrecio(bloque) {
        var fijo = bloque.querySelector('[data-precio-fijo]').checked;
        bloque.querySelector('[data-precio-pesos]').classList.toggle('d-none', !fijo);
        bloque.querySelector('[data-precio-dolares]').classList.toggle('d-none', fijo);
        var cotizacion = parseFloat(bloque.getAttribute('data-cotizacion'));
        var usd = numero(bloque.querySelector('[data-precio-usd]').value);
        var destino = bloque.querySelector('[data-precio-calculado]');
        if (!cotizacion) {
            destino.textContent = 'Todavía no hay cotización del dólar: se mostrará "Consultar" hasta que la haya.';
        } else if (usd !== null) {
            destino.textContent = 'Hoy se ve como $ ' + Math.round(usd * cotizacion).toLocaleString('es-AR')
                + ' (dólar a $ ' + cotizacion.toLocaleString('es-AR') + ').';
        } else {
            destino.textContent = 'Se pasa a pesos con el dólar del día ($ ' + cotizacion.toLocaleString('es-AR') + ').';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-precio]').forEach(actualizarPrecio);
    });
    ['change', 'input'].forEach(function (evento) {
        document.addEventListener(evento, function (e) {
            var bloque = e.target.closest && e.target.closest('[data-precio]');
            if (bloque) { actualizarPrecio(bloque); }
        });
    });

    /* ---------- Compresión de fotos antes de subir ----------
       Los campos con data-comprimir="1600" achican cada foto a ese lado máximo y la pasan a JPG
       en el navegador: una foto de celular de 8 MB queda en unos 300 KB. El servidor la vuelve a
       procesar igual (si el navegador no puede comprimir, se sube la original). */

    var comprimidas = new WeakSet();

    function comprimir(archivo, lado) {
        if (!/^image\/(jpeg|png|webp)$/.test(archivo.type) || comprimidas.has(archivo) || !window.createImageBitmap) {
            return Promise.resolve(archivo);
        }
        // imageOrientation: la foto sale derecha según el EXIF del celular
        return createImageBitmap(archivo, { imageOrientation: 'from-image' }).then(function (bmp) {
            var escala = Math.min(1, lado / Math.max(bmp.width, bmp.height));
            // Ya es chica y liviana: se deja como está
            if (escala === 1 && archivo.size < 600 * 1024) {
                comprimidas.add(archivo);
                return archivo;
            }
            var lienzo = document.createElement('canvas');
            lienzo.width = Math.round(bmp.width * escala);
            lienzo.height = Math.round(bmp.height * escala);
            var ctx = lienzo.getContext('2d');
            ctx.fillStyle = '#fff';   // fondo blanco para PNG con transparencia
            ctx.fillRect(0, 0, lienzo.width, lienzo.height);
            ctx.drawImage(bmp, 0, 0, lienzo.width, lienzo.height);
            return new Promise(function (listo) {
                lienzo.toBlob(function (blob) {
                    if (!blob || blob.size >= archivo.size) {   // no mejoró: queda la original
                        comprimidas.add(archivo);
                        return listo(archivo);
                    }
                    var nueva = new File([blob], archivo.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' });
                    comprimidas.add(nueva);
                    listo(nueva);
                }, 'image/jpeg', 0.85);
            });
        }).catch(function () { return archivo; });
    }

    document.addEventListener('change', function (e) {
        var campo = e.target;
        if (!campo.matches('input[type=file][data-comprimir]') || !window.DataTransfer) { return; }
        var archivos = Array.prototype.slice.call(campo.files);
        if (!archivos.length || archivos.every(function (f) { return comprimidas.has(f); })) { return; }

        // Mientras se preparan las fotos no se puede enviar el formulario
        var botones = campo.form ? campo.form.querySelectorAll('[type=submit]') : [];
        var aviso = campo.parentElement.querySelector('.aviso-comprimiendo');
        if (!aviso) {
            aviso = document.createElement('div');
            aviso.className = 'aviso-comprimiendo small text-body-secondary mt-1';
            campo.insertAdjacentElement('afterend', aviso);
        }
        aviso.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Preparando las fotos…';
        botones.forEach(function (b) { b.disabled = true; });

        var lado = parseInt(campo.getAttribute('data-comprimir'), 10) || 1600;
        var antes = archivos.reduce(function (t, f) { return t + f.size; }, 0);
        Promise.all(archivos.map(function (f) { return comprimir(f, lado); })).then(function (listas) {
            var juntas = new DataTransfer();
            listas.forEach(function (f) { juntas.items.add(f); });
            campo.files = juntas.files;
            var despues = listas.reduce(function (t, f) { return t + f.size; }, 0);
            var mb = function (b) { return (b / 1048576).toFixed(1).replace('.', ','); };
            aviso.textContent = despues < antes
                ? 'Fotos listas: ' + mb(antes) + ' MB → ' + mb(despues) + ' MB'
                : '';
            botones.forEach(function (b) { b.disabled = false; });
            campo.dispatchEvent(new Event('change', { bubbles: true }));   // actualiza la vista previa
        });
    });

    /* ---------- Botón "Sacar foto": suma cada foto de la cámara al campo de fotos ---------- */

    document.addEventListener('change', function (e) {
        if (!e.target.matches('[data-camara-para]')) { return; }
        var camara = e.target;
        var destino = document.querySelector(camara.getAttribute('data-camara-para'));
        if (!destino || !camara.files.length || !window.DataTransfer) { return; }
        var juntas = new DataTransfer();
        Array.prototype.forEach.call(destino.files, function (f) { juntas.items.add(f); });
        Array.prototype.forEach.call(camara.files, function (f) { juntas.items.add(f); });
        destino.files = juntas.files;
        camara.value = '';   // listo para sacar otra
        destino.dispatchEvent(new Event('change', { bubbles: true }));   // actualiza la vista previa
    });

    return { mensaje: mensaje };
})();
