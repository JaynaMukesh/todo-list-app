function niceBytes(a) { let b = 0, c = parseInt(a, 10) || 0; for (; 1024 <= c && ++b;)c /= 1024; return c.toFixed(10 > c && 0 < b ? 1 : 0) + " " + ["bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"][b] }
function wxp_dismiss_modal() {
    setTimeout(function () {
        $('body').removeAttr('class');
        $('[class="modal-backdrop fade show"]').remove();
    }, 200)
}
function dismiss_modal(modal_id, root) {
    if (modal_id != null) {
        $(`[id=${modal_id}]`).modal('toggle')
        if (Wxp_DOM.checkElm('.modal-backdrop')) {
            $('.modal-backdrop').remove()
            $('body').removeAttr('style')
        }
        setTimeout(() => {
            $(root).remove()
        }, 200)
    } else if (modal_id == null) {
        if (Wxp_DOM.checkElm('.modal-backdrop')) {
            $('.modal-backdrop').remove()
            $('body').removeAttr('style')
        }
        setTimeout(() => {
            $(root).remove()
        }, 200)
    }
}
var app = {
    _init_() {
        var domID = this.domID();
        const _domID = domID;
        this.preloadComponents(domID);
        this.initializeSettings(domID);
        this.swc();
    },
    swc() {
        if ("serviceWorker" in navigator) {
            window.addEventListener("load", function () {
                navigator.serviceWorker
                    .register("./serviceWorker.js")
                    .then(res => console.log("service worker registered"))
                    .catch(err => console.log("service worker not registered", err))
            })
        }

    },
    domID() {
        return makeid(10)
    },
    preloadComponents(domID) {
        Wxp_DOM.createElement('', `wxp-dom-${domID}`, 'wxp-app', async () => {
            var resp = await this.renderSkeleton(domID);
            this.dismiss_preloader();
            if (resp == 'verified') {
                //
            } else {
                Wxp_DOM.render(`<div class="alert alert-info" uk-alert>
                <a class="alert alert-close" uk-close></a>
                <h3>Woah,</h3>
                <p>Something went wrong! Please <a href="">reload</a> this page to continue!</p>
            </div>`, `wxp-dom-${domID}`);
            }
        });
    },
    initializeSettings(domID) {
        $('[wxpclid="settings"]').click(() => {
            var settings = this.renderHTMLModal({
                title: 'App Settings'
            })
            var localData = JSON.parse(localStorage.WXP_TODO);
            var blob = new Blob([$(`ul`).text()], { type: "text/plain" });
            var url = window.URL.createObjectURL(blob);
            console.log(`Cached Data: "${url}"`);
            var storageOccupied = niceBytes(blob.size);
            settings.modal.render(`<div class="container m-5 p-2 rounded mx-auto bg-light shadow">
            <div class="row m-1 p-3">
                <div class="col col-11 mx-auto">
                        <div class="row mb-6">
                            <!--begin::Label-->
                            <label class="col-lg-2 col-form-label fw-bold fs-6">
                                <span class="">Data Storage Type:</span>
                            </label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-10 fv-row">
                            <label class="form-check form-check-inline form-check-solid me-5">
                                <span class="fw-bold ps-2 fs-6"><h3>${localData.storage}</h3></span>
                            </label>
                            </div>
                           
                        </div>
                        <br>
                        <p>Storage occupied: ${storageOccupied}</p>
                        <div class="d-flex flex-stack pt-15 float-right">
                            <div class="mr-2"></div>
                            <div>
                                <button wxpclid="uninstall" class="btn btn-primary">Uninstall/Re-install</button>
                            </div>
                        </div>
                </div>
            </div>
            <div class="col mx-auto"></div>
        </div>`);
            $('[wxpclid="uninstall"]').click(() => {
                if (confirm("You will lose all your saved tasks! Are you sure to proceed?")) {
                    settings.modal.close();
                    var x = Wxp_DOM.progressBar("10%", '[class="container m-5 p-2 rounded mx-auto bg-light shadow"]', { text: true, height: "30px" })
                    setTimeout(() => {
                        x.update("40%");
                        wipe().then(r => {
                            setTimeout(() => {
                                x.update("90%")
                                setTimeout(() => { x.dismiss(); window.location.reload() }, 500);
                            }, 1500);
                        })
                    }, 800);
                    function wipe() {
                        return new Promise((resolve, reject) => {
                            var localData = JSON.parse(localStorage.WXP_TODO);
                            localStorage.removeItem("WXP_TODO");
                            if (localData.storage == "cloud") {
                                app.compRequest({
                                    action: 'wipe'
                                }).then(resp => {
                                    resolve('verified');
                                })
                            } else {
                                setTimeout(() => resolve('verified'), 700);
                            }
                        })
                    }
                }
            })
        })
    },
    renderSkeleton(domID) {
        return new Promise((resolve, reject) => {
            var res = '',
                rej = '';
            if (!domID) {
                rej = 'Invalid DOM ID!';
            } else if (!this.validate_DOMID(domID)) {
                rej = 'Invalid DOM ID!';
            }
            if (rej == '' && res == '') {
                //rendering page elements
                Wxp_DOM.createElement(`<div class="container m-5 p-2 rounded mx-auto bg-light shadow">
                <!-- App title section -->
                <div class="row m-1 p-4">
                    <div class="col">
                        <div class="p-1 h1 text-primary text-center mx-auto display-inline-block">
                            <i class="fa fa-check bg-primary text-white rounded p-2"></i>
                            <u>My Todo-s</u>
                        </div>
                    </div>
                </div>
                <!-- Create todo section -->
                <div class="row m-1 p-3">
                    <div class="col col-11 mx-auto">
                    <form method="POST" action="<wxp-self>" wxpclid="form">
                        <div class="row bg-white rounded shadow-sm p-2 add-todo-wrapper align-items-center justify-content-center">
                            <div class="col">
                                <input class="form-control form-control-lg border-0 add-todo-input bg-transparent rounded" wxpdata="todo" type="text" placeholder="Add new ..">
                            </div>
                            <div class="col-auto px-0 mx-0 mr-2">
                                <button wxpclid="add-new" type="submit" class="btn btn-primary">Add</button>
                            </div>
                        </div>
                        <div class="linear-progress-material small" wxpclid="progress" style="width: 100%; height: 3px; display: none;"><div class="bar bar1"></div><div class="bar bar2"></div></div>
                    </form>
                    </div>
                </div>
                <div class="p-2 mx-4 border-black-25 border-bottom"></div>
                
                <!-- View options section -->
                <div class="row m-1 p-3 px-5 justify-content-end">
                    
                    <div class="col-auto d-flex align-items-center px-1 pr-3">
                        <i style="font-size: 20px;" class="fa fa-cog fa-6 text-info btn mx-0 px-0 pl-1" wxp-tooltip data-toggle="tooltip" data-placement="top" wxpclid="settings" title="Settings"></i>
                    </div>
                </div>
                <!-- Todo list section -->
                <div class="col mx-auto">
                        <ul wxpclid="${domID}"></ul>
                    </div>
            </div>`, 'wxp-content-renderer', `wxp-dom-${domID}`, () => {
                    this.init_components(domID);
                    /*Handling form submission*/
                    $('[wxpclid="form"]').submit(e => {
                        getInput();
                        return false;
                    })
                    function getInput() {
                        var newData = $('[wxpdata="todo"]').val();
                        if (!newData || newData == '') {
                            alert('Please type something to add!');
                        } else {
                            var _t = makeid(20);
                            $('[wxpdata="todo"]').val('')
                            Wxp_DOM.append(`<li>${newData}</li>`, `[wxpclid="${domID}"]`);
                            var _domID = makeid(10);
                            $(`[wxpclid="${domID}"]`).attr('wxpclid', _domID);
                            domID = _domID;
                            app.init_components(domID, true);
                            var ld = app.validateLocalStorage();
                            if (ld == 'install') {
                                // window.location.reload();
                            } else {
                                var t = ld.data[makeid(5)] = {
                                    task: newData,
                                    timestamp: new Date(),
                                    isCompleted: false
                                };
                                var localData = JSON.parse(localStorage.WXP_TODO);
                                if (localData.storage == "cloud") {
                                    app.insertIntoDB({
                                        action: 'insert',
                                        content: t
                                    });
                                } else if (localData.storage == "local") {
                                    localStorage.WXP_TODO = JSON.stringify(ld);
                                }
                            }
                        }
                    }
                });
                resolve('verified');
            } else {
                reject(rej);
            }
        })
    },
    updateState(data_id) {
        var isCompleted = false;
        if (Wxp_DOM.checkElm(`[__secure="${data_id}"].checked`)) {
            $(`[__secure="${data_id}"]`).removeAttr('class');
            isCompleted = false;
        } else {
            $(`[__secure="${data_id}"]`).attr('class', 'checked');
            isCompleted = true;
        }
        var task = $(`[wxp-data="${data_id}"]`).text();
        //update task state in localdata
        var localData = JSON.parse(localStorage.WXP_TODO);
        if (localData.storage == "cloud") {
            app.updateCloudStorage({
                task: task,
                isCompleted: isCompleted,
                action: 'update'
            });
        } else if (localData.storage == "local") {
            $.each(localData.data, (k, v) => {
                if (v.task == task) {
                    v.isCompleted = isCompleted;
                }
            })
            localStorage.WXP_TODO = JSON.stringify(localData);
        }
    },
    install_app() {
        return new Promise((resolve, reject) => {
            var m = this.renderHTMLModal({
                title: 'Install Wizard',
                width: '1000',
                onClose: resolve
            })
            m.modal.render(`<div class="container m-5 p-2 rounded mx-auto bg-light shadow">
            <div class="row m-1 p-3">
                <div class="col col-11 mx-auto">
                    <h5 class="text-center">Welcome to My TODO-s App!</h5>
                    <form method="POST" action="<wxp-self>" wxpclid="installer">
                        <div class="row mb-6">
                            <!--begin::Label-->
                            <label class="col-lg-2 col-form-label fw-bold fs-6">
                                <span class="">Choose how to store your data</span>
                            </label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-10 fv-row">
                            <!--begin::Option-->
                            <label class="form-check form-check-inline form-check-solid me-5">
                                <input class="form-check-input" name="storage" type="radio" value="local">
                                <span class="fw-bold ps-2 fs-6">Local device</span>
                            </label>
                            <!--end::Option-->
                            </div>
                            <!--end::Col-->
                            <!--begin::Col-->
                            <div class="col-lg-2"></div>
                            <div class="col-lg-10 fv-row">
                            <!--begin::Option-->
                            <label class="form-check form-check-inline form-check-solid">
                                <input class="form-check-input" name="storage" type="radio" value="cloud">
                                <span class="fw-bold ps-2 fs-6">Cloud (encrypted)</span>
                            </label>
                            <!--end::Option-->
                            </div>
                            <!--end::Col-->
                        </div>
                        <br>
                        <p>By clicking on the <b>install</b> button you acknowledge our <a href="https://webxspark.com/terms-of-service/" rel="noopener noreferrer" target="_blank">Terms</a> & <a href="https://webxspark.com/privacy%20policy/" rel="noopener noreferrer" target="_blank">privacy policy</a></p>
                        <div class="d-flex flex-stack pt-15 float-right">
                            <div class="mr-2"></div>
                            <div>
                                <button type="submit" class="btn btn-primary">Install</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col mx-auto"></div>
        </div>`);
            $('[wxpclid="installer"]').submit((e) => {
                var storage = $('[name="storage"]:checked').val();
                if (!storage) {
                    alert('Please select a storage method to install app!');
                } else {
                    var data = {
                        app_id: makeid(10),
                        cache: false,
                        data: {}
                    };
                    if (storage == "local") {
                        data.storage = storage;
                    } else if (storage == "cloud") {
                        data.storage = storage;
                        app.insertElm(app.domID(), app.fetchCloudResource());
                    } else {
                        window.location.reload();
                    }
                    localStorage.setItem('WXP_TODO', JSON.stringify(data));
                    m.modal.close();
                    resolve('verified');

                }
                return false;
            })
            /*For debugging purposes*/
            // setTimeout(() => {
            // }, 500);
        })
    },
    fetchCloudResource() {
        return new Promise((resolve, reject) => {
            app.progressBar('show');
            this.compRequest({
                action: 'fetchAll'
            }, "POST").then(response => {
                if (response.error) {
                    alert(response.error);
                } else {
                    resolve(response.data);
                }
                app.progressBar('hide');
            })
        });
    },
    updateCloudStorage(data) {
        app.progressBar('show');
        this.compRequest(data, "POST").then(response => {
            if (response.error) {
                alert(response.error);
            }
            app.progressBar('hide');
        })
    },
    insertIntoDB(data) {
        app.progressBar('show');
        this.compRequest(data, "POST").then(response => {
            if (response.error) {
                alert(response.error);
            }
            app.progressBar('hide');
        })
    },
    deleteFromCloudStorage(data) {
        app.progressBar('show');
        this.compRequest(data, "POST").then(response => {
            if (response.error) {
                alert(response.error);
            }
            app.progressBar('hide');
        })
    },
    remove(data_id) {
        var task = $(`[wxp-data="${data_id}"]`).text(),
            localData = JSON.parse(localStorage.WXP_TODO);
        if (localData.storage == "cloud") {
            this.deleteFromCloudStorage({
                task: task,
                action: 'delete'
            });
        } else if (localData.storage == "local") {
            $.each(localData.data, (k, v) => {
                if (v.task == task) {
                    delete (localData.data[k]);
                }
            })
            localStorage.WXP_TODO = JSON.stringify(localData);
        }
    },
    init_delBtn() {
        setTimeout(() => {
            $.each(($('li')), (k, v) => {
                var chkAttr = $(v).attr('__secure');
                if (typeof chkAttr !== 'undefined' && chkAttr !== false) { } else {
                    var data_id = makeid(20);
                    $(v).attr('__secure', data_id);
                    var text = ($(v).html());
                    $(v).html(`<wxp-li-elm wxp-data="${data_id}" onclick="app.updateState('${data_id}')">${text}</wxp-li-elm><wxp-nonce-${data_id}></wxp-nonce-${data_id}>`);
                    $(`wxp-nonce-${data_id}`).html(`<span class="close" data-toggle="tooltip" title="Remove" onclick="app.remove('${data_id}')" __del="${data_id}" >\u00D7</span>`)
                }
            })
            /*Delete btn function*/
            $('[__del]').click(function () {
                var _tmp_elm = $(`[__secure="${$(this).attr('__del')}"]`),
                    data_id = $(this).attr('__del');
                _tmp_elm.css({ 'right': '0px', 'left': '' }).animate({
                    'right': '100px',
                    'opacity': '0'
                });
                setTimeout(() => {
                    _tmp_elm.remove();
                }, 650);
            })
        }, 500);
    },
    insertElm(domID, data) {
        var check = this.validateLocalStorage();
        if (check == 'install') { window.location.reload(); }
        function __liAppend(dict) {
            $.each(dict, (key, val) => {
                var _class = '';
                if (val.isCompleted == true || val.isCompleted == "true") {
                    _class = 'checked';
                }
                Wxp_DOM.append(`<li class="${_class}">${val.task}</li>`, `ul[wxpclid="${domID}"]`);
            })
        }
        __liAppend(data);
        this.init_delBtn();
    },
    async init_components(domID, safeUpdate = false) {
        var check = this.validateLocalStorage();
        if (check == 'install') {
            this.install_app().then(resp => {
                if (resp != 'verified') {
                    Wxp_DOM.render(`<div class="alert alert-info" uk-alert>
                <a class="alert alert-close" uk-close></a>
                <h3>App not Installed!</h3>
                <p>Something went wrong when setting up the application! Please <a href="">reload</a> this page <b>or</b> try again in a different device!</p>
            </div>`, `wxp-dom-${domID}`);
                }
            });

        } else {
            /*Autoload-tasks*/
            if (safeUpdate == false) {
                if (check.storage == "cloud") {
                    app.fetchCloudResource().then(cdata => {
                        app.insertElm(domID, cdata);
                    })
                } else if (check.storage == "local") {
                    app.insertElm(domID, check.data);
                }
            } else {
                app.init_delBtn();
            }
        }

        $('[data-toggle="tooltip"]').tooltip();
    },
    compRequest(data, method = "POST") {
        return $.ajax({
            url: './components/tasks.php?alt=json&token=' + makeid(10),
            method: method,
            data: data,
            dataType: 'JSON',
            error: (a, b) => this.handleAjaxError(a, b)
        })
    },
    checkStorageType() {
        var ld = this.validateLocalStorage();
        if (ld.storage) {
            return ld.storage;
        } else {
            // window.location.reload();
        }
    },
    validateLocalStorage() {
        if (!localStorage.WXP_TODO) {
            return 'install';
        } else {
            var localData = (localStorage.WXP_TODO);
            if (!this.isJson(localData)) {
                return 'install'
            } else {
                localData = JSON.parse(localData);
                return localData;
            }
        }
    },
    isJson(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    },
    dismiss_preloader() {
        $('wxp-preloader').fadeOut('slow');
        setTimeout(() => {
            $('wxp-preloader').remove();
        }, 500);
    },
    validate_DOMID(domID) {
        if (Wxp_DOM.checkElm(`wxp-dom-${domID}`)) {
            return true;
        } else {
            return false;
        }
    },
    progressBar(action) {
        var q = $('[wxpclid="progress"]');
        if (action == "show") {
            q.show();
        } else if (action == "hide") {
            q.hide();
        }
    },
    handleAjaxError(jqXHR, exception) {
        var error_msg = ''
        if (jqXHR.status === 0) {
            error_msg += "You're offline. Check your connection and server status.";
        } else if (jqXHR.status == 404) {
            error_msg += 'Error: The API request returns code 404 [File Not Found]';
        } else if (jqXHR.status == 500) {
            error_msg += 'Error: The API request returns code 500 [Internal Server Error]';
        } else if (exception === 'parsererror') {
            error_msg += 'Requested JSON parse failed!';
        } else if (exception === 'timeout') {
            error_msg +=
                'Connection Time out. Please check your connection and server status!';
        } else if (exception === 'abort') {
            error_msg += 'AJAX Request aborted! Please try again later!';
        } else {
            error_msg += jqXHR.responseText;
        }
        alert('', error_msg)
    },
    renderHTMLModal(props) {
        function render_bs_modal_HTML(title, id, data_load, loader = false, width = '650px') {
            var data = '';
            if (loader === false) {
                data += `<div class="modal fade" id="${id}" tabindex="-1" aria-hidden="true"> <div class="modal-dialog modal-dialog-centered mw-${width}"> <div class="modal-content"> <div class="modal-header"> <h2>${title}</h2> <div class="btn btn-sm btn-icon btn-active-color-primary" onclick="wxp_dismiss_modal()" wxp-modal-dismiss="${id}" data-bs-dismiss="modal"><span class="svg-icon svg-icon-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" /><rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" /></svg></span></div></div><div class="modal-body scroll-y mx-5 mx-xl-15 my-7"> <div id="${data_load}"></div></div></div></div></div>`;
            } else {
                data += `<div class="modal fade" id="${id}" tabindex="-1" aria-hidden="true"> <div class="modal-dialog modal-dialog-centered  modal-lg "> <div class="modal-content"> <div class="modal-header"> <h2>${title}</h2> <div class="btn btn-sm btn-icon btn-active-color-primary" onclick="wxp_dismiss_modal()" wxp-modal-dismiss="${id}" data-bs-dismiss="modal"><span class="svg-icon svg-icon-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" /><rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" /></svg></span></div></div>${render_linear_loader("100%")}</div><div class="modal-body scroll-y mx-5 mx-xl-15 my-7"> <div id="${data_load}"></div></div></div></div></div>`;
            }
            return data;
        }
        var props = props ? props : false
        var title = props.title ? props.title : 'Title'
        var modal_id = makeid(10),
            data_load = '__secure-' + makeid(10),
            modal = render_bs_modal_HTML(
                title,
                modal_id,
                data_load,
                (loader = false),
                (width = '1000px')
            )
        var root = 'wxp-secure-root_' + makeid(5)
        if (!Wxp_DOM.checkElm(root)) {
            Wxp_DOM.createElement('', root, 'body')
        }
        let modal_root = $(root)
        Wxp_DOM.render(modal, modal_root)
        $(`[id=${modal_id}]`)
            .modal({
                backdrop: 'static',
                keyboard: false
            })
            .modal('toggle')
        $('[data-bs-dismiss="modal"]').click(() => {
            removeRoot()
            if (props.onClose) {
                props.onClose();
            }
        })
        if (props.loader) {
            var html = `<wxp-content-renderer>
              <wxp-loader>
                  <div id="kt_content_container" class="container-xxl text-center">
                      <wxp-div style="display: flex;align-items: center;justify-content: center;"><wxp-blob></wxp-blob></wxp-div>
                  </div>
              </wxp-loader>
          </wxp-content-renderer>`;
            Wxp_DOM.render(html, '#' + data_load)
        }
        function removeRoot() {
            setTimeout(() => {
                modal_root.remove()
            }, 200)
        }

        return {
            root: {
                modal: root,
                data: '#' + data_load,
                close_button: `[wxp-modal-dismiss="${modal_id}"]`
            },
            modal: {
                id: modal_id,
                close: () => {
                    dismiss_modal(modal_id, root)
                    return 'component destroyed'
                },
                render: html => {
                    Wxp_DOM.render(html, '#' + data_load)
                    return 'State updated'
                },
                append: html => {
                    Wxp_DOM.append(html, '#' + data_load)
                    return 'State updated'
                },
                toggle: () => {
                    $(`[id=${modal_id}]`).modal('toggle')
                    return 'State updated'
                },
                blockUI: (props = {}) => {
                    Wxp_DOM.blockUI({
                        text: props.text ? props.text : 'Please wait...',
                        action: props.action ? props.action : 'block',
                        target: props.target ? props.target : `[wxpclid="modal_${modal_id}"]`
                    })
                    return 'State updated'
                }
            }
        }
    }
}
app._init_();