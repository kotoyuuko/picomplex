@extends('layouts.app')

@section('content')
    <div class="container" id="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="jumbotron" id="dropbox" style="border:2px dashed silver;">
                    <div v-show="!isUploading">
                        <h3>拖拽图片到这里上传</h3>
                        <p style="font-size: 16px">或者</p>
                        <p><a href="javascript:;" class="btn btn-primary btn-sm" v-on:click="selectFile">选择文件</a></p>
                        <form id="form" style="visibility: hidden" v-on:change="changeFile">
                            <input type="file" name="file" accept="image/*">
                        </form>
                    </div>
                    <div v-show="isUploading">
                        <h2>上传中...</h2>
                    </div>
                </div>
                <form class="form-inline">
                    <div v-show="url" class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">url</div>
                            <input id="url" type="text" class="form-control" v-model="url" placeholder="上传成功后的链接"
                                   style="width: 400px;">
                            <div class="input-group-addon">
                                <img class="clip" width="13" data-clipboard-target="#url" src="img/clippy.svg"
                                     alt="Copy to clipboard">
                            </div>
                        </div>
                    </div>
                    <a v-show="url" class="btn btn-default" target="_blank" :href="url">新窗口打开</a>
                </form>
                <div id="preview"><img :src="url" v-show="url" style="max-width: 750px;"></div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdnjs.cat.net/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cat.net/ajax/libs/vue/2.4.0/vue.js"></script>
    <script src="https://cdnjs.cat.net/ajax/libs/axios/0.16.2/axios.min.js"></script>
    <script src="https://cdnjs.cat.net/ajax/libs/clipboard.js/1.7.1/clipboard.min.js"></script>
    <script>
        $(function () {
            $(document).on({
                dragleave: function (e) {    //拖离
                    e.preventDefault();
                },
                drop: function (e) {  //拖后放
                    e.preventDefault();
                },
                dragenter: function (e) {    //拖进
                    e.preventDefault();
                },
                dragover: function (e) {    //拖来拖去
                    e.preventDefault();
                }
            });
            var dropbox = document.getElementById('dropbox');
            dropbox.addEventListener("drop", function (e) {
                e.preventDefault(); //取消默认浏览器拖拽效果
                dropbox.style.backgroundColor = '#eee';
                var fileList = e.dataTransfer.files;
                //检测是否是拖拽文件到页面的操作
                if (fileList.length == 0) {
                    return false;
                }
                //检测文件是不是图片
                if(fileList[0].type.indexOf('image') === -1){
                    alert("您拖的不是图片！");
                    return false;
                }
                vm.form = new FormData();
                vm.form.append('file', fileList[0]);
                console.log(form);
                vm.upload();
            }, false);
            dropbox.addEventListener("dragover", function (e) {
                dropbox.style.borderColor = 'gray';
                dropbox.style.backgroundColor = 'white';
            }, false);
            dropbox.addEventListener("dragenter", function (e) {
                dropbox.style.borderColor = 'gray';
                dropbox.style.backgroundColor = 'white';
            }, false);
            dropbox.addEventListener("dragleave", function (e) {
                dropbox.style.borderColor = 'silver';
                dropbox.style.backgroundColor = '#eee';
            }, false);
        });
        var vm = new Vue({
            el: '#container',
            data: {
                form: null,
                url: null,
                text: null,
                isUploading: false
            },
            methods: {
                selectFile: function () {
                    $("input[type=file]").click();
                },
                changeFile: function () {
                    var form = document.getElementById('form');
                    this.form = new FormData(form);
                    if ($("input[type=file]").val()) {
                        this.upload();
                    }
                },
                upload: function () {
                    if(this.form.get('file').size > 5 * 1024 * 1024){
                        alert("上传大小不能超过5MB");
                        return false;
                    }
                    this.isUploading = true;
                    $("#form").submit();
                },
            },
            mounted: function () {
                new Clipboard('.clip');
                $('#form').submit(function (e) {
                    vm.url = null;
                    axios.post('/api/upload', vm.form).then(function (response) {
                        var data = response.data;
                        if (data.code === 200) {
                            vm.url = data.data;
                        } else {
                            alert(data.data);
                        }
                        vm.isUploading = false;
                    }).catch(function (error) {
                        alert(error);
                        vm.isUploading = false;
                    });
                    e.preventDefault();
                })
            }
        })
    </script>
@endsection
