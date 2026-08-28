file 8.3 

<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>UploadiFive - رفع الملفات</title>
<!-- استخدام jQuery من CDN موثوق -->
<script src="https://code.jquery.com/jquery-1.7.1.min.js" type="text/javascript"></script>
<script src="jquery.uploadifive.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="uploadifive.css">
<style type="text/css">
body {
	font: 13px Arial, Helvetica, Sans-serif;
	direction: rtl;
	text-align: right;
}
.uploadifive-button {
	float: right;
	margin-left: 10px;
	margin-right: 0;
}
#queue {
	border: 1px solid #E5E5E5;
	height: 177px;
	overflow: auto;
	margin-bottom: 10px;
	padding: 0 3px 3px;
	width: 300px;
}
.uploadifive-queue-item {
	background-color: #f5f5f5;
	border-bottom: 1px solid #ddd;
	padding: 5px;
	margin: 3px 0;
}
.uploadifive-queue-item .close {
	color: #ff0000;
	float: left;
	cursor: pointer;
}
.uploadifive-queue-item .fileinfo {
	float: right;
}
.uploadifive-queue-item .progress-bar {
	background-color: #4CAF50;
	height: 5px;
	width: 0%;
	border-radius: 3px;
	margin-top: 5px;
}
.uploadifive-queue-item.complete {
	background-color: #d4edda;
}
.uploadifive-queue-item.error {
	background-color: #f8d7da;
}
</style>
</head>

<body>
	<h1>تجربة UploadiFive - رفع الملفات</h1>
	
	<!-- رسائل التحميل -->
	<div id="message" style="display: none; padding: 10px; margin-bottom: 10px; border-radius: 4px;"></div>
	
	<form>
		<div id="queue"></div>
		<input id="file_upload" name="file_upload" type="file" multiple="true">
		<a style="position: relative; top: 8px; margin-right: 10px; cursor: pointer; display: inline-block; padding: 5px 10px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 3px;" href="javascript:$('#file_upload').uploadifive('upload')">رفع الملفات</a>
		<a style="position: relative; top: 8px; margin-right: 10px; cursor: pointer; display: inline-block; padding: 5px 10px; background-color: #f44336; color: white; text-decoration: none; border-radius: 3px;" href="javascript:$('#file_upload').uploadifive('cancel')">إلغاء الكل</a>
	</form>

	<script type="text/javascript">
		$(function() {
			
			// دالة عرض الرسائل
			function showMessage(msg, type) {
				var $msg = $('#message');
				$msg.html(msg).show();
				if (type === 'success') {
					$msg.css({'background-color': '#d4edda', 'color': '#155724', 'border': '1px solid #c3e6cb'});
				} else if (type === 'error') {
					$msg.css({'background-color': '#f8d7da', 'color': '#721c24', 'border': '1px solid #f5c6cb'});
				}
				setTimeout(function() {
					$msg.fadeOut();
				}, 5000);
			}
			
			// تهيئة UploadiFive
			$('#file_upload').uploadifive({
				'auto'         : false, // لا تبدأ الرفع تلقائياً
				'formData'     : {
					'timestamp' : '<?php echo time(); ?>',
					'token'     : '<?php echo md5('unique_salt' . time()); ?>',
					'test'      : 'something'
				},
				'queueID'      : 'queue', // معرف عنصر قائمة الانتظار
				'uploadScript' : 'uploadifive.php', // سكريبت معالجة الرفع
				'fileObjName'  : 'Filedata', // اسم حقل الملف
				'method'       : 'post',
				'buttonText'   : 'اختر ملفات',
				'buttonClass'  : 'uploadifive-button',
				'width'        : 100,
				'height'       : 30,
				'multi'        : true, // رفع ملفات متعددة
				'fileSizeLimit': 5242880, // 5 ميجابايت
				'fileType'     : ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'], // أنواع الملفات المسموحة
				'fileTypeDesc' : 'الصور وملفات PDF',
				'removeCompleted': true, // إزالة الملفات الناجحة من القائمة
				'simUploadLimit': 2, // عدد الملفات التي يتم رفعها في وقت واحد
				'checkScript'  : 'uploadify-check-exists.php', // سكريبت التحقق من وجود الملف
				'requeueErrors': false, // لا تعيد محاولة الملفات الفاشلة تلقائياً
				
				// عند بدء رفع ملف
				'onUploadStart' : function(file) {
					console.log('بدء رفع: ' + file.name);
				},
				
				// عند تقدم رفع الملف
				'onProgress' : function(file, event) {
					if (event.lengthComputable) {
						var percent = Math.round((event.loaded / event.total) * 100);
						// يمكن تحديث شريط التقدم هنا
						console.log(file.name + ': ' + percent + '%');
					}
				},
				
				// عند اكتمال رفع ملف
				'onUploadComplete' : function(file, data) {
					console.log('اكتمل رفع: ' + file.name);
					console.log('البيانات المستلمة:', data);
					
					try {
						var response = JSON.parse(data);
						if (response.success) {
							showMessage('تم رفع الملف "' + file.name + '" بنجاح', 'success');
						} else {
							showMessage('فشل رفع الملف "' + file.name + '": ' + response.message, 'error');
						}
					} catch(e) {
						// إذا لم تكن الاستجابة JSON
						if (data == '1') {
							showMessage('تم رفع الملف "' + file.name + '" بنجاح', 'success');
						} else {
							showMessage('فشل رفع الملف "' + file.name + '"', 'error');
						}
					}
				},
				
				// عند رفع جميع الملفات
				'onUploadQueueComplete' : function(queueData) {
					console.log('تم رفع جميع الملفات');
					console.log('الملفات الناجحة:', queueData.uploadsSuccessful);
					console.log('الملفات الفاشلة:', queueData.uploadsFailed);
					
					if (queueData.uploadsFailed > 0) {
						showMessage('تم رفع ' + queueData.uploadsSuccessful + ' ملفات بنجاح، وفشل ' + queueData.uploadsFailed + ' ملفات', 'error');
					} else if (queueData.uploadsSuccessful > 0) {
						showMessage('تم رفع جميع الملفات بنجاح (' + queueData.uploadsSuccessful + ')', 'success');
					}
				},
				
				// عند خطأ
				'onError' : function(errorType, file, data) {
					console.error('خطأ:', errorType, file.name, data);
					showMessage('خطأ في رفع الملف "' + file.name + '": ' + data, 'error');
				},
				
				// عند إضافة ملف إلى قائمة الانتظار
				'onAddQueueItem' : function(file) {
					console.log('تمت إضافة الملف: ' + file.name);
				},
				
				// عند إلغاء ملف
				'onCancel' : function(file) {
					console.log('تم إلغاء الملف: ' + file.name);
				},
				
				// عند مسح قائمة الانتظار
				'onClearQueue' : function(queueItemCount) {
					console.log('تم مسح قائمة الانتظار');
				}
			});
			
			// إضافة زر إلغاء الكل
			$('#file_upload').on('uploadifivecancelall', function() {
				console.log('تم إلغاء جميع الملفات');
			});
			
			// دالة مساعدة لإضافة تنسيق أفضل لقائمة الانتظار
			$('#file_upload').on('uploadifiveaddqueueitem', function(event, file) {
				var $item = $('#file_upload').data('uploadifive').queueData.files[file.id].item;
				$item.addClass('uploadifive-queue-item');
				$item.append('<div class="progress-bar" style="width:0%"></div>');
				$item.find('.close').text('×');
			});
			
			// تحديث شريط التقدم
			$('#file_upload').on('uploadifiveprogress', function(event, file, event) {
				if (event.lengthComputable) {
					var percent = Math.round((event.loaded / event.total) * 100);
					$('#file_upload').data('uploadifive').queueData.files[file.id].item.find('.progress-bar').css('width', percent + '%');
				}
			});
			
			// تمييز الملفات المكتملة
			$('#file_upload').on('uploadifiveuploadcomplete', function(event, file) {
				$('#file_upload').data('uploadifive').queueData.files[file.id].item.addClass('complete');
			});
			
			// تمييز الملفات الفاشلة
			$('#file_upload').on('uploadifiveerror', function(event, file) {
				$('#file_upload').data('uploadifive').queueData.files[file.id].item.addClass('error');
			});
		});
	</script>
</body>
</html>