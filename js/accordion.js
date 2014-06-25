// JavaScript Document

$(function(){

    $('.howto').click(function(){
        $(this).next().slideToggle('slow', function(){
        });
    });	
});