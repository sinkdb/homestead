"use strict";angular.module("roomDamages",[]),angular.module("hmsAngularApp",["ngResource","roomDamages","ngRoute"]).config(["$routeProvider","$httpProvider",function(a,b){b.defaults.useXDomain=!0,b.defaults.withCredentials=!0,a.when("/",{templateUrl:"views/checkout.html",controller:"CheckoutCtrl"}).otherwise({redirectTo:"/"})}]);var myApp=angular.module("hmsAngularApp");myApp.controller("CheckoutCtrl",["$scope","roomDamageBroker","roomDamageResident","$http",function(a,b,c,d){b.getDamages().then(function(b){a.damages=b.data}),a.dmgTypes=b.getDamageTypes(),a.newDamages=[],a.residents=c.getResidents(),a.student=c.getStudent(),a.assignment=c.getAssignment(),a.checkin=c.getCheckin(),a.addDamage=function(){var b=angular.copy(a.residents);a.newDamages.push({residents:b})},a.removeDamage=function(b){a.newDamages.splice(b,1)},a.data={},a.data.keyCode="",a.data.keyReturned=-1,a.data.properCheckout=-1,a.triedSubmit=!1;var e=function(){a.checkout_form.keyCode.$setValidity("keyReturn",1!=a.data.keyReturned||!!a.data.keyCode),a.checkout_form.keyReturned.$setValidity("keyReturn",-1!=a.data.keyReturned)};a.$watch("data.keyReturned",e),a.$watch("data.keyCode",e);var f=function(){a.checkout_form.properCheckout.$setValidity("properCheckout",-1!=a.data.properCheckout)};a.$watch("data.properCheckout",f),a.submitHandler=function(){a.triedSubmit=!0;for(var c=0;c<a.newDamages.length;c++){var e=!1;for(var f in a.newDamages[c].residents)a.newDamages[c].residents[f].selected&&(e=!0);if(!e)return alert("Please select at least one student who is responsible for each damage."),void 0}if(!a.checkout_form.$valid)return alert("Cannot complete checkout because the form is incomplete.  Please check the form for errors."),void 0;var g=b.getBaseLocation();d.post(g+"index.php?module=hms&action=CheckoutFormSubmit&ajax=true",{bannerId:a.student.studentId,checkinId:a.checkin.id,keyCode:a.data.keyCode,keyReturned:a.data.keyReturned,newDamages:a.newDamages,properCheckout:a.data.properCheckout}).success(function(a,b,c){window.location=c("location")}).error(function(){alert("Something went wrong while saving this checkout. Please contact the Housing Assignments Office.")})}}]),angular.module("roomDamages").provider("roomDamageBroker",function(){function a(a,d){this.getDamages=function(){return a.get(c+"?module=hms&ajax=true&action=GetRoomDamages&bed_id="+d)},this.getDamageTypes=function(){return b},this.getBaseLocation=function(){return c}}var b=null,c=null;this.setDamageTypes=function(a){b=a},this.setLocation=function(a){c=a},this.$get=["$http","roomDamageResident",function(b,c){return new a(b,c.getCheckin().bed_id)}]}),angular.module("roomDamages").provider("roomDamageResident",function(){function a(){this.getResidents=function(){return b},this.getStudent=function(){return c},this.getAssignment=function(){return d},this.getCheckin=function(){return e}}var b=null,c=null,d=null,e=null;this.setResidents=function(a){b=a},this.setStudent=function(a){c=a},this.setAssignment=function(a){d=a},this.setCheckin=function(a){e=a},this.$get=function(){return new a}});