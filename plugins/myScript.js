$(document).ready(function(){
         
         // Ensure no right-click and keydown events
         document.addEventListener('contextmenu', function(event) {
            event.preventDefault();
          });
      
          document.onkeydown = function(e) {
            if (e.keyCode === 123 || (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 85))) {
              return false;
            }
          };

        });
