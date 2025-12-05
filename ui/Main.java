import java.util.Scanner;

public class Main {
    public static void main(String[] args) {
        int numeroDia;
        double precioProducto;
        Scanner t = new Scanner(System.in);
        System.out.print("Ingrese el numero del dia de la semana(Lun:1, mar:2, mie:3,jue:4,vie:5 ,sab:6, dom:7)\n");
        numeroDia = t.nextInt();
        t.next();
        System.out.print("Ingrese el precio del producto :\n");
        precioProducto = t.nextDouble();
        t.next();

            if((precioProducto>=50 & precioProducto<=80) && numeroDia==1){
                System.out.println("Se permite el cambio del producto aqui");            
            }else if((precioProducto>120 && numeroDia!=3)){
                System.out.println("Se permite el cambio del producto sin 3");}
            
            }else{
                System.out.println("NOooooooooooo permite el cambio del producto");
            }
        t.close();


    }
}