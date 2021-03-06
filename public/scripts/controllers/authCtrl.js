'use strict';

appModule.controller('AuthController', 
	['$scope', '$location', '$rootScope','accountFactory',
	function($scope, $location, $rootScope, accountFactory){
		//account model
		$scope.genders = ['Male', 'Female', 'Custom'];
		$scope.usernm;
		$scope.passwd;
		$scope.repasswd;
		$scope.email;
		$scope.firstNm;
		$scope.lastNm;
		$scope.dob;
		$scope.gender;
		$scope.customGender;

		$scope.verifyAndCreateAccount = function(){
			$scope.verifyPasswds();
			$scope.verifyUnique($scope.createAccount);
		};

		$scope.verifyPasswds = function(){
			var contentMismatch, insufficientSize;

			//handle the case where passwords don't match
			if($scope.passwd !== $scope.repasswd){
				contentMismatch = true;
			}
			if($scope.passwd.length < 8 && $scope.repasswd.length < 8){
				insufficientSize = true;
			}

			//do something
			if(contentMismatch || insufficientSize){
				if(contentMismatch) util.renderAlert("", "Passwords don't match", "alert-danger");
				if(insufficientSize) util.renderAlert("", "Password is too short.", "alert-danger")
			}
		};

		$scope.verifyUnique = function(optCallback){
			accountFactory.isUnique('username', $scope.usernm)
			.success(function(data){
				if(data == 'false'){
					//handle where username isn't unique
					util.renderAlert("", $scope.usernm + " is taken!", "alert-danger");
				}
				else if (data == 'true') {
					if(optCallback) optCallback();
				}
			})
			.error(function(error){});
		};

		$scope.createAccount = function(){
			if(!$scope.customGender) $scope.customGender = "Bootiful";
			var gender = $scope.gender === 'Custom' ? $scope.customGender : $scope.gender;

			accountFactory.createAccount(
				$scope.usernm, $scope.passwd,
				$scope.email, $scope.firstNm, $scope.lastNm,
				$scope.dob, gender
				)
			.success(function(data){
				console.log(data);
				if(data === 'true'){
					$scope.login();
				}	
			})
			.error(function(error){
				console.log(error);
			});
		};

		$scope.login = function(){
			accountFactory.login($scope.usernm, $scope.passwd)
			.success(function(data){
				if(data == 'true') {
					$location.path('/');
					var msg = data;
					$rootScope.$broadcast('logAction',msg);
				}
				else util.renderAlert("Oops!", "Your username or password is incorrect.", "alert-danger");
			})
			.error(function(error){ console.log(error); });
		};

		$scope.logout = function(){
			accountFactory.logout()
			.success(function(data){
				console.log(data);
				$location.path('#/');
			})
			.error(function(errors){
				console.log(errors);
			});
		};
	}
]);