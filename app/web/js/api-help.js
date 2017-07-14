/**
 * Created by fa2m on 07.12.15.
 */
(function() {

    var TestApiUni = {

        jsonContainer : '',
        jsonContainerValue : '',
        singleInputClassName : '',
        formModel : {},

        init: function (params) {
            this.jsonContainer = params.jsonContainer;
            this.jsonContainerValue = $(this.jsonContainer).val();
            this.singleInputClassName = params.singleInputClassName;
            this.arrayInputClassName = params.arrayInputClassName;

            this.createJson();
            this.multiItemsDeleteListener();
            this.multiItemsCreateListener();
        },
        processSingleItems : function (params) {
            var items = $(this.singleInputClassName);

            items.each(function () {
                var item = $(this);
                var fields = item.find('input[type="text"]');
                fields.each(function(){
                    var field = $(this);
                    TestApiUni.formModel[field.attr('name')] = field.val();
                });
            });


        },
        processArrayItems : function (params) {
            var items = $(this.arrayInputClassName);

            items.each(function () {
                var item = $(this);
                var dataset_name = item.attr('data-array-label');
                var fields = item.find('input[type="text"]');
                var dataset = [];
                fields.each(function(){
                    var field = $(this);
                    dataset.push(field.val());
                });
                TestApiUni.formModel[dataset_name] = dataset;
            });

        },
        createJson : function (params) {
            this.processSingleItems();
            this.processArrayItems();
            $(this.jsonContainer).val(JSON.stringify(this.formModel));
            this.jsonContainerValue = JSON.stringify(this.formModel);
        },
        reverseJson: function () {
            var strJson = $.parseJSON($(this.jsonContainer).val());
            var containers = Object.keys(strJson);
            containers.forEach(function (item) {
                if (typeof strJson[item] == 'object') {
                    var dataContainers = $('div[data-array-label="' + item + '"]').find('input[type="text"]');
                    for (var i = 0; i < dataContainers.length; i++) {
                        $(dataContainers[i]).val(strJson[item][i]);
                    }
                } else {
                    $('#' + item).val(strJson[item]);
                }
            }.bind(this));
        },
        multiItemsDeleteListener: function (params) {
            $('.delete-button').unbind().click(function () {
                $(this).closest('.form-group').remove();
                TestApiUni.createJson();
            });
        },
        multiItemsCreateListener: function (params) {
            $('.btn-add-array-item').click(function () {
                var fields = $(this).closest('.array-input').find('.form-group');
                var field = fields[0];
                var newField = $(field).clone();
                $(newField).find('input').val('');
                $(this).closest('.array-input').find('.multi-fields-container').append(newField);
                TestApiUni.multiItemsDeleteListener();
            });
        }
    };

    var requestLib = {
        responseContainer : '#responseData',
        jsonContainer : '#jsonTextArea',

        send : function () {
            var data = $(this.jsonContainer).val();
            if ($.parseJSON(data) !== null && $.parseJSON(data) !== undefined) {
                console.log($.parseJSON(data));
                var url = $('form').attr('action');
                console.log(url);
                $.ajax({
                    url: url,
                    data: {
                        json : data
                    },
                    method: 'POST',
                    success : function(response) {
                        console.log(JSON.stringify(response));
                        $(requestLib.responseContainer).html(JSON.stringify(response));
                    }
                });
            }
        },

        buttonListener : function () {
            $('#send-request').click(function () {
                requestLib.send();
            });
        }
    };

    $(document).on('input', 'input[type="text"]', function() {
        TestApiUni.createJson();
    });

    $(document).on('input', '#jsonTextArea', function() {
        TestApiUni.reverseJson();
    });

    requestLib.buttonListener();

    TestApiUni.init({
        jsonContainer: '#jsonTextArea',
        singleInputClassName: '.single-input',
        arrayInputClassName: '.array-input'
    });
})();
