
import java.sql.*;


public class DBConnect {
    private String user = "root";
    private String pass = "";
    private String host = "jdbc:mysql://localhost:3306/test";
    private String sql;
    private Connection conn;
    private Statement stmt;
    private ResultSet rs;
    private PreparedStatement pstmt;
    private boolean connected = false;
    public DBConnect(){
        connect();
    }
    private void connect(){
        try {
           conn = DriverManager.getConnection(host, user, pass);
           //System.out.println("Conexion establecida");
           connected = true;
           /*stmt = conn.createStatement();
           rs = stmt.executeQuery(sql);
           while(rs.next()){
               System.out.println(rs.getString(2) + " " + rs.getInt(3) + " " + rs.getDate(4));
           } */   
        } catch (SQLException e) {
            System.out.println(e.getMessage());
        }
    }
    
    public boolean isConnected(){
        return connected;
    }
    
    
    public ResultSet select() throws SQLException{
        sql = "SELECT * FROM puntuaciones2 ORDER BY puntuacion DESC LIMIT 5";
        if(connected){
           stmt = conn.createStatement();
           rs = stmt.executeQuery(sql);
           return rs;
        }else{
            rs = null;
            return rs;
        }
    }
    
    public void insert(String nombre , int puntuacion) throws SQLException{
        sql = "INSERT INTO puntuaciones2(nombre, puntuacion) VALUES (? , ?)";
        if(connected){
            pstmt = conn.prepareStatement(sql);
            pstmt.setString(1, nombre);
            pstmt.setInt(2, puntuacion);
            pstmt.execute();
        }
           
    
    }
}
